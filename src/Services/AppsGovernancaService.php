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

        try {
            $user->employee->remember_token = null;
            $user->employee->motivo = $motivo ?: ($user->employee->motivo ?? 'Suspenso via apps-api');
            $user->employee->save();
        } catch (Throwable) {
            //
        }

        return [
            'usuario_id' => (int) $user->id,
            'login' => $user->login,
            'tokens_revogados' => $tokens,
            'sessoes_web_revogadas' => $sessoes,
            'ativo' => false,
        ];
    }

    public function reativarUsuario(User $user): array
    {
        $user->employee->ativo = 1;
        $user->employee->data_expiracao = null;
        $user->employee->save();
        $user->ativo = 1;
        $user->save();

        return [
            'usuario_id' => (int) $user->id,
            'login' => $user->login,
            'ativo' => true,
        ];
    }

    /**
     * Desliga município para apps (+ alinha Suspender cliente do i-Educar) e revoga todos os tokens apps.
     */
    public function bloquearMunicipio(?string $mensagem = null): array
    {
        $this->definirActiveOnIeducar(false, $mensagem);
        Config::set('apps-api.enabled', false);

        $revogados = $this->revogarTodosTokensApps();

        return [
            'apps_habilitados' => false,
            'active_on_ieducar' => false,
            'tokens_apps_revogados' => $revogados,
            'mensagem' => $mensagem ?: $this->mensagemSuspensao(),
        ];
    }

    public function liberarMunicipio(): array
    {
        $this->definirActiveOnIeducar(true);
        Config::set('apps-api.enabled', true);

        return [
            'apps_habilitados' => true,
            'active_on_ieducar' => true,
        ];
    }

    public function revogarTodosTokensApps(): int
    {
        if (! Schema::hasTable('personal_access_tokens')) {
            return 0;
        }

        try {
            return (int) DB::table('personal_access_tokens')
                ->where('name', 'like', 'apps-%')
                ->delete();
        } catch (Throwable) {
            return 0;
        }
    }

    private function apagarSessoesWeb(int $userId): int
    {
        if (! Schema::hasTable('sessions')) {
            return 0;
        }

        try {
            return (int) DB::table('sessions')->where('user_id', $userId)->delete();
        } catch (Throwable) {
            return 0;
        }
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
