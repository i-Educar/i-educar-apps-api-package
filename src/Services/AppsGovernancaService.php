<?php

namespace iEducar\Packages\BuritiAppsApi\Services;

use App\Models\LegacyGeneralConfiguration;
use App\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Governança de acesso apps + web (Onda 5).
 */
final class AppsGovernancaService
{
    public function appsHabilitados(): bool
    {
        if (! (bool) config('apps-api.enabled', true)) {
            return false;
        }

        $active = Config::get('legacy.config.active_on_ieducar');
        if ($active === false || $active === 0 || $active === '0') {
            return false;
        }

        return true;
    }

    public function mensagemSuspensao(): string
    {
        $msg = Config::get('legacy.config.ieducar_suspension_message');
        if (is_string($msg) && trim($msg) !== '') {
            return $msg;
        }

        return (string) config('apps-api.suspension_message');
    }

    public function usuarioPodeUsarApp(User $user): bool
    {
        try {
            if (method_exists($user, 'isActive') && ! $user->isActive()) {
                return false;
            }
            if (method_exists($user, 'isExpired') && $user->isExpired()) {
                return false;
            }
            if (method_exists($user, 'isNotStarted') && $user->isNotStarted()) {
                return false;
            }
        } catch (Throwable) {
            return false;
        }

        return (int) ($user->employee->ativo ?? 0) === 1;
    }

    /**
     * Suspende usuário: inativa + revoga tokens Sanctum + sessões web.
     * Na próxima request o listener AuthenticatedUser força logout web se a sessão file ainda existir.
     */
    public function suspenderUsuario(User $user, ?string $motivo = null): array
    {
        $tokens = 0;
        $sessoes = 0;

        if (method_exists($user, 'tokens')) {
            $tokens = (int) $user->tokens()->count();
            $user->tokens()->delete();
        }

        $sessoes = $this->apagarSessoesWeb((int) $user->id);

        if (method_exists($user, 'disable')) {
            $user->disable();
        } else {
            $user->employee->ativo = 0;
            $user->employee->save();
            $user->ativo = 0;
            $user->save();
        }

        $motivoFinal = $motivo ?: ($user->employee->motivo ?? 'Suspenso via apps-api');
        try {
            $user->employee->remember_token = null;
            $user->employee->motivo = $motivoFinal;
            $user->employee->save();
        } catch (Throwable) {
            //
        }

        $result = [
            'usuario_id' => (int) $user->id,
            'login' => $user->login,
            'tokens_revogados' => $tokens,
            'sessoes_web_revogadas' => $sessoes,
            'ativo' => false,
            'acesso_bloqueado' => true,
        ];

        $this->registrarEvento('suspender_usuario', $motivoFinal, (int) $user->id, (string) $user->login, $result);

        return $result;
    }

    /**
     * @param  list<int>  $usuarioIds
     * @return array{ok:int,falhas:list<array{id:int,erro:string}>,itens:list<array<string,mixed>>}
     */
    public function suspenderUsuarios(array $usuarioIds, ?string $motivo = null, ?User $ator = null): array
    {
        $escopo = app(AppsUsuarioEscopoService::class);
        $ok = 0;
        $falhas = [];
        $itens = [];

        foreach (array_unique(array_map('intval', $usuarioIds)) as $id) {
            if ($id < 1) {
                continue;
            }
            $user = User::query()->find($id);
            if ($user === null) {
                $falhas[] = ['id' => $id, 'erro' => 'nao_encontrado'];

                continue;
            }
            if ($ator !== null && ! $escopo->podeGerenciar($ator, $user)) {
                $falhas[] = ['id' => $id, 'erro' => 'fora_do_escopo'];

                continue;
            }
            try {
                $itens[] = $this->suspenderUsuario($user, $motivo);
                $ok++;
            } catch (Throwable $e) {
                $falhas[] = ['id' => $id, 'erro' => $e->getMessage()];
            }
        }

        return compact('ok', 'falhas', 'itens');
    }

    public function reativarUsuario(User $user): array
    {
        $user->employee->ativo = 1;
        $user->employee->data_expiracao = null;
        $user->employee->save();
        $user->ativo = 1;
        $user->save();

        $result = [
            'usuario_id' => (int) $user->id,
            'login' => $user->login,
            'ativo' => true,
        ];

        $this->registrarEvento('reativar_usuario', 'Usuário reativado', (int) $user->id, (string) $user->login, $result);

        return $result;
    }

    /**
     * Desliga município para apps (+ alinha Suspender cliente do i-Educar) e revoga todos os tokens apps.
     */
    public function bloquearMunicipio(?string $mensagem = null): array
    {
        $this->definirActiveOnIeducar(false, $mensagem);
        Config::set('apps-api.enabled', false);

        $revogados = $this->revogarTodosTokensApps();

        $result = [
            'apps_habilitados' => false,
            'active_on_ieducar' => false,
            'tokens_apps_revogados' => $revogados,
            'mensagem' => $mensagem ?: $this->mensagemSuspensao(),
        ];

        $this->registrarEvento('bloquear_municipio', $result['mensagem'], null, null, $result);

        return $result;
    }

    public function liberarMunicipio(): array
    {
        $this->definirActiveOnIeducar(true);
        Config::set('apps-api.enabled', true);

        $result = [
            'apps_habilitados' => true,
            'active_on_ieducar' => true,
        ];

        $this->registrarEvento('liberar_municipio', 'Município liberado para apps', null, null, $result);

        return $result;
    }

    public function revogarToken(int $tokenId): bool
    {
        if (! Schema::hasTable('personal_access_tokens')) {
            return false;
        }

        $row = DB::table('personal_access_tokens')
            ->where('id', $tokenId)
            ->where(function ($q): void {
                $q->where('name', 'like', 'apps-%')
                    ->orWhere('tokenable_type', 'like', '%User');
            })
            ->first();

        if ($row === null) {
            return false;
        }

        DB::table('personal_access_tokens')->where('id', $tokenId)->delete();

        $this->registrarEvento(
            'revogar_token',
            'Token #'.$tokenId.' ('.$row->name.')',
            (int) $row->tokenable_id,
            null,
            ['token_id' => $tokenId, 'name' => $row->name]
        );

        return true;
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function registrarEvento(
        string $acao,
        ?string $detalhe = null,
        ?int $alvoUsuarioId = null,
        ?string $alvoLogin = null,
        array $meta = [],
    ): void {
        if (! Schema::hasTable('apps_api.evento_governanca')) {
            return;
        }

        try {
            $ator = auth()->user();
            DB::table('apps_api.evento_governanca')->insert([
                'acao' => $acao,
                'ator_id' => $ator?->getAuthIdentifier(),
                'ator_login' => $ator?->login,
                'alvo_usuario_id' => $alvoUsuarioId,
                'alvo_login' => $alvoLogin,
                'detalhe' => $detalhe !== null ? mb_substr($detalhe, 0, 500) : null,
                'meta' => $meta !== [] ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
                'created_at' => now(),
            ]);
        } catch (Throwable) {
            //
        }
    }

    public function revogarTodosTokensApps(): int
    {
        if (! Schema::hasTable('personal_access_tokens')) {
            return 0;
        }

        try {
            // Preferência: prefixo apps-; também remove tokens Sanctum de User (legado sem prefixo).
            return (int) DB::table('personal_access_tokens')
                ->where(function ($q): void {
                    $q->where('name', 'like', 'apps-%')
                        ->orWhere('tokenable_type', 'like', '%User');
                })
                ->delete();
        } catch (Throwable) {
            return 0;
        }
    }

    private function apagarSessoesWeb(int $userId): int
    {
        $apagadas = 0;

        if (Schema::hasTable('sessions')) {
            try {
                $apagadas += (int) DB::table('sessions')->where('user_id', $userId)->delete();
            } catch (Throwable) {
                //
            }
        }

        // SESSION_DRIVER=file não indexa por user_id; o bloqueio efetivo web vem de
        // User::disable() + AuthenticatedUser (logout se inativo na próxima request).

        return $apagadas;
    }

    private function definirActiveOnIeducar(bool $ativo, ?string $mensagem = null): void
    {
        $valor = $ativo ? 1 : 0;

        try {
            if (Schema::hasTable('pmieducar.configuracoes_gerais')) {
                $cfg = LegacyGeneralConfiguration::query()->first();
                if ($cfg !== null) {
                    $cfg->active_on_ieducar = $valor;
                    if ($mensagem !== null && $mensagem !== '') {
                        $cfg->ieducar_suspension_message = $mensagem;
                    }
                    $cfg->save();
                }
            }
        } catch (Throwable) {
            //
        }

        try {
            if (class_exists(\App\Setting::class) && Schema::hasTable('settings')) {
                \App\Setting::query()
                    ->where('key', 'legacy.config.active_on_ieducar')
                    ->update(['value' => $ativo ? 'true' : 'false']);
                if ($mensagem !== null && $mensagem !== '') {
                    \App\Setting::query()
                        ->where('key', 'legacy.config.ieducar_suspension_message')
                        ->update(['value' => $mensagem]);
                }
            }
        } catch (Throwable) {
            //
        }

        Config::set('legacy.config.active_on_ieducar', $ativo);
        if ($mensagem !== null && $mensagem !== '') {
            Config::set('legacy.config.ieducar_suspension_message', $mensagem);
        }
    }
}
