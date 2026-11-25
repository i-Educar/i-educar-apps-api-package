<?php

namespace iEducar\Packages\BuritiAppsApi\Services;

use App\Models\LegacyUserType;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Listagem e autorização de alvo para governança, respeitando nível do ator.
 *
 * Admin (1): rede inteira
 * Institucional (2): usuários de escola/biblioteca (não admin/institucional)
 * Escolar (4): apenas usuários vinculados às mesmas escolas do ator
 */
final class AppsUsuarioEscopoService
{
    /**
     * @return list<array{id:int,login:string,nome:?string,nivel:?int,nivel_nome:?string,escolas:list<string>}>
     */
    public function usuariosSelecionaveis(?User $ator, ?string $busca = null, int $limite = 300): array
    {
        if ($ator === null || ! Schema::hasTable('pmieducar.usuario')) {
            return [];
        }

        try {
            $q = DB::table('pmieducar.usuario as u')
                ->join('portal.funcionario as f', 'f.ref_cod_pessoa_fj', '=', 'u.cod_usuario')
                ->join('pmieducar.tipo_usuario as tu', 'tu.cod_tipo_usuario', '=', 'u.ref_cod_tipo_usuario')
                ->leftJoin('cadastro.pessoa as p', 'p.idpes', '=', 'u.cod_usuario')
                ->where('u.ativo', 1)
                ->where('f.ativo', 1)
                ->where('u.cod_usuario', '<>', (int) $ator->id);

            $this->aplicarEscopoListagem($q, $ator);

            $termo = trim((string) $busca);
            if ($termo !== '') {
                $like = '%'.$termo.'%';
                $q->where(function ($w) use ($like): void {
                    $w->where('f.matricula', 'ilike', $like)
                        ->orWhere('p.nome', 'ilike', $like)
                        ->orWhereRaw('CAST(u.cod_usuario AS TEXT) ILIKE ?', [$like]);
                });
            }

            $rows = $q->orderBy('p.nome')
                ->orderBy('f.matricula')
                ->limit($limite)
                ->get([
                    'u.cod_usuario as id',
                    'f.matricula as login',
                    'p.nome',
                    'tu.nivel',
                    'tu.nm_tipo as nivel_nome',
                ]);
        } catch (Throwable) {
            return [];
        }

        $ids = $rows->pluck('id')->map(static fn ($id) => (int) $id)->all();
        $escolasPorUsuario = $this->escolasDosUsuarios($ids);

        $out = [];
        foreach ($rows as $r) {
            $id = (int) $r->id;
            $out[] = [
                'id' => $id,
                'login' => (string) $r->login,
                'nome' => isset($r->nome) ? (string) $r->nome : null,
                'nivel' => isset($r->nivel) ? (int) $r->nivel : null,
                'nivel_nome' => isset($r->nivel_nome) ? (string) $r->nivel_nome : null,
                'escolas' => $escolasPorUsuario[$id] ?? [],
            ];
        }

        return $out;
    }

    public function podeGerenciar(User $ator, User $alvo): bool
    {
        if ((int) $ator->id === (int) $alvo->id) {
            return false;
        }

        if (method_exists($ator, 'isAdmin') && $ator->isAdmin()) {
            return true;
        }

        $nivelAlvo = (int) ($alvo->type->level ?? $alvo->type->nivel ?? 0);

        if (method_exists($ator, 'isInstitutional') && $ator->isInstitutional()) {
            // Institucional gerencia apenas níveis escola/biblioteca
            return in_array($nivelAlvo, [LegacyUserType::LEVEL_SCHOOLING, LegacyUserType::LEVEL_LIBRARY], true);
        }

        if (method_exists($ator, 'isSchooling') && $ator->isSchooling()) {
            if (! in_array($nivelAlvo, [LegacyUserType::LEVEL_SCHOOLING, LegacyUserType::LEVEL_LIBRARY], true)) {
                return false;
            }

            return $this->compartilhamEscola($ator, $alvo);
        }

        return false;
    }

    /**
     * @param  \Illuminate\Database\Query\Builder  $q
     */
    private function aplicarEscopoListagem($q, User $ator): void
    {
        if (method_exists($ator, 'isAdmin') && $ator->isAdmin()) {
            return;
        }

        if (method_exists($ator, 'isInstitutional') && $ator->isInstitutional()) {
            $q->whereIn('tu.nivel', [LegacyUserType::LEVEL_SCHOOLING, LegacyUserType::LEVEL_LIBRARY]);

            return;
        }

        if (method_exists($ator, 'isSchooling') && $ator->isSchooling()) {
            $q->whereIn('tu.nivel', [LegacyUserType::LEVEL_SCHOOLING, LegacyUserType::LEVEL_LIBRARY]);
            $escolas = $this->idsEscolasDoUsuario((int) $ator->id);
            if ($escolas === []) {
                $q->whereRaw('1 = 0');

                return;
            }
            $q->whereExists(function ($sub) use ($escolas): void {
                $sub->select(DB::raw(1))
                    ->from('pmieducar.escola_usuario as eu')
                    ->whereColumn('eu.ref_cod_usuario', 'u.cod_usuario')
                    ->whereIn('eu.ref_cod_escola', $escolas);
            });

            return;
        }

        // Biblioteca / outros: sem listagem de suspensão
        $q->whereRaw('1 = 0');
    }

    /** @return list<int> */
    private function idsEscolasDoUsuario(int $usuarioId): array
    {
        if (! Schema::hasTable('pmieducar.escola_usuario')) {
            return [];
        }
        try {
            return DB::table('pmieducar.escola_usuario')
                ->where('ref_cod_usuario', $usuarioId)
                ->pluck('ref_cod_escola')
                ->map(static fn ($id) => (int) $id)
                ->all();
        } catch (Throwable) {
            return [];
        }
    }

    private function compartilhamEscola(User $ator, User $alvo): bool
    {
        $a = $this->idsEscolasDoUsuario((int) $ator->id);
        $b = $this->idsEscolasDoUsuario((int) $alvo->id);
        if ($a === [] || $b === []) {
            return false;
        }

        return count(array_intersect($a, $b)) > 0;
    }

    /**
     * @param  list<int>  $usuarioIds
     * @return array<int, list<string>>
     */
    private function escolasDosUsuarios(array $usuarioIds): array
    {
        if ($usuarioIds === [] || ! Schema::hasTable('pmieducar.escola_usuario')) {
            return [];
        }

        try {
            $rows = DB::table('pmieducar.escola_usuario as eu')
                ->leftJoin('pmieducar.escola as e', 'e.cod_escola', '=', 'eu.ref_cod_escola')
                ->leftJoin('cadastro.pessoa as pe', 'pe.idpes', '=', 'e.ref_idpes')
                ->whereIn('eu.ref_cod_usuario', $usuarioIds)
                ->get(['eu.ref_cod_usuario', 'pe.nome', 'e.cod_escola']);
        } catch (Throwable) {
            return [];
        }

        $map = [];
        foreach ($rows as $r) {
            $uid = (int) $r->ref_cod_usuario;
            $nome = trim((string) ($r->nome ?? ''));
            $label = $nome !== '' ? $nome : ('Escola #'.(int) $r->cod_escola);
            $map[$uid] ??= [];
            if (! in_array($label, $map[$uid], true)) {
                $map[$uid][] = $label;
            }
        }

        return $map;
    }
}
