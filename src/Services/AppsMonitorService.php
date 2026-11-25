<?php

namespace iEducar\Packages\BuritiAppsApi\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Leituras para o dashboard web de governança da Apps API.
 */
final class AppsMonitorService
{
    /**
     * @return list<array{id:int,name:string,abilities:?string,usuario_id:int,login:string,created_at:?string,last_used_at:?string,expires_at:?string,expirado:bool}>
     */
    public function tokensAtivos(int $limite = 100): array
    {
        if (! Schema::hasTable('personal_access_tokens')) {
            return [];
        }

        try {
            $rows = DB::table('personal_access_tokens as t')
                ->leftJoin('pmieducar.usuario as u', function ($j): void {
                    $j->on('u.cod_usuario', '=', 't.tokenable_id')
                        ->where('t.tokenable_type', 'like', '%User');
                })
                ->where(function ($w): void {
                    $w->where('t.name', 'like', 'apps-%')
                        ->orWhere('t.tokenable_type', 'like', '%User');
                })
                ->orderByDesc('t.last_used_at')
                ->orderByDesc('t.created_at')
                ->limit($limite)
                ->get([
                    't.id',
                    't.name',
                    't.abilities',
                    't.tokenable_id',
                    't.created_at',
                    't.last_used_at',
                    't.expires_at',
                    'u.login',
                ]);
        } catch (\Throwable) {
            return [];
        }

        $agora = now();
        $out = [];
        foreach ($rows as $r) {
            $expira = $r->expires_at ? (string) $r->expires_at : null;
            $expirado = $expira !== null && $agora->gt($expira);
            $out[] = [
                'id' => (int) $r->id,
                'name' => (string) $r->name,
                'abilities' => $r->abilities !== null ? (string) $r->abilities : null,
                'usuario_id' => (int) $r->tokenable_id,
                'login' => (string) ($r->login ?? ('#'.$r->tokenable_id)),
                'created_at' => $r->created_at !== null ? (string) $r->created_at : null,
                'last_used_at' => $r->last_used_at !== null ? (string) $r->last_used_at : null,
                'expires_at' => $expira,
                'expirado' => $expirado,
            ];
        }

        return $out;
    }

    /**
     * @return list<array{id:int,login:string,nome:?string,motivo:?string,atualizado:?string,suspenso_por:?string,suspenso_em:?string}>
     */
    public function usuariosSuspensos(int $limite = 100): array
    {
        if (! Schema::hasTable('pmieducar.usuario') || ! Schema::hasTable('portal.funcionario')) {
            return [];
        }

        try {
            $rows = DB::table('pmieducar.usuario as u')
                ->join('portal.funcionario as f', 'f.ref_cod_pessoa_fj', '=', 'u.cod_usuario')
                ->leftJoin('cadastro.pessoa as p', 'p.idpes', '=', 'u.cod_usuario')
                ->where(function ($q): void {
                    $q->where('u.ativo', 0)->orWhere('f.ativo', 0);
                })
                ->orderByDesc('f.data_expiracao')
                ->orderByDesc('u.cod_usuario')
                ->limit($limite)
                ->get([
                    'u.cod_usuario as id',
                    'f.matricula as login',
                    'p.nome',
                    'f.motivo',
                    'f.data_expiracao',
                ]);
        } catch (\Throwable) {
            try {
                $rows = DB::table('pmieducar.usuario as u')
                    ->join('portal.funcionario as f', 'f.ref_cod_pessoa_fj', '=', 'u.cod_usuario')
                    ->where(function ($q): void {
                        $q->where('u.ativo', 0)->orWhere('f.ativo', 0);
                    })
                    ->orderByDesc('u.cod_usuario')
                    ->limit($limite)
                    ->get(['u.cod_usuario as id', 'f.matricula as login', 'f.motivo']);
            } catch (\Throwable) {
                return [];
            }
        }

        $auditoria = $this->ultimoAtoresSuspensao(
            $rows->pluck('id')->map(static fn ($id) => (int) $id)->all()
        );

        $out = [];
        foreach ($rows as $r) {
            $id = (int) $r->id;
            $audit = $auditoria[$id] ?? null;
            $out[] = [
                'id' => $id,
                'login' => (string) ($r->login ?? ''),
                'nome' => isset($r->nome) ? (string) $r->nome : null,
                'motivo' => isset($r->motivo) && $r->motivo !== null && $r->motivo !== ''
                    ? (string) $r->motivo
                    : null,
                'atualizado' => isset($r->data_expiracao) && $r->data_expiracao !== null
                    ? (string) $r->data_expiracao
                    : null,
                'suspenso_por' => $audit['ator_login'] ?? null,
                'suspenso_em' => $audit['created_at'] ?? null,
            ];
        }

        return $out;
    }

    /**
     * @param  list<int>  $usuarioIds
     * @return array<int, array{ator_login:?string,created_at:?string}>
     */
    private function ultimoAtoresSuspensao(array $usuarioIds): array
    {
        if ($usuarioIds === [] || ! Schema::hasTable('apps_api.evento_governanca')) {
            return [];
        }

        try {
            $rows = DB::table('apps_api.evento_governanca')
                ->where('acao', 'suspender_usuario')
                ->whereIn('alvo_usuario_id', $usuarioIds)
                ->orderByDesc('id')
                ->get(['alvo_usuario_id', 'ator_login', 'created_at']);
        } catch (\Throwable) {
            return [];
        }

        $map = [];
        foreach ($rows as $r) {
            $id = (int) $r->alvo_usuario_id;
            if (isset($map[$id])) {
                continue;
            }
            $map[$id] = [
                'ator_login' => $r->ator_login !== null ? (string) $r->ator_login : null,
                'created_at' => $r->created_at !== null ? (string) $r->created_at : null,
            ];
        }

        return $map;
    }

    /**
     * @return list<array{id:int,acao:string,ator_login:?string,alvo_login:?string,detalhe:?string,created_at:?string,meta:array<string,mixed>}>
     */
    public function historicoBloqueios(int $limite = 80): array
    {
        if (! Schema::hasTable('apps_api.evento_governanca')) {
            return [];
        }

        try {
            $rows = DB::table('apps_api.evento_governanca')
                ->orderByDesc('id')
                ->limit($limite)
                ->get();
        } catch (\Throwable) {
            return [];
        }

        $out = [];
        foreach ($rows as $r) {
            $meta = [];
            if (! empty($r->meta)) {
                $decoded = is_string($r->meta) ? json_decode($r->meta, true) : $r->meta;
                $meta = is_array($decoded) ? $decoded : [];
            }
            $out[] = [
                'id' => (int) $r->id,
                'acao' => (string) $r->acao,
                'ator_login' => $r->ator_login !== null ? (string) $r->ator_login : null,
                'alvo_login' => $r->alvo_login !== null ? (string) $r->alvo_login : null,
                'detalhe' => $r->detalhe !== null ? (string) $r->detalhe : null,
                'created_at' => $r->created_at !== null ? (string) $r->created_at : null,
                'meta' => $meta,
            ];
        }

        return $out;
    }

    /** @return array{tokens:int,suspensos:int,eventos:int,apps_on:bool} */
    public function resumo(): array
    {
        $gov = app(AppsGovernancaService::class);

        return [
            'tokens' => count($this->tokensAtivos(500)),
            'suspensos' => count($this->usuariosSuspensos(500)),
            'eventos' => $this->contarEventos(),
            'apps_on' => $gov->appsHabilitados(),
        ];
    }

    private function contarEventos(): int
    {
        if (! Schema::hasTable('apps_api.evento_governanca')) {
            return 0;
        }
        try {
            return (int) DB::table('apps_api.evento_governanca')->count();
        } catch (\Throwable) {
            return 0;
        }
    }
}
