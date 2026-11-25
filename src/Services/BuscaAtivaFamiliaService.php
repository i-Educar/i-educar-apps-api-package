<?php

namespace iEducar\Packages\BuritiAppsApi\Services;

use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Ponte Apps API → domínio busca_ativa.* com escopo do responsável.
 */
final class BuscaAtivaFamiliaService
{
    public function __construct(private readonly FamiliaConsultaService $familia) {}

    /**
     * @return array{data:list<array<string,mixed>>,meta:array<string,mixed>}
     */
    public function processos(User $user): array
    {
        if (! Schema::hasTable('busca_ativa.processo')) {
            return ['data' => [], 'meta' => ['aviso' => 'schema_busca_ativa_ausente', 'status' => 503]];
        }

        $alunoIds = $this->idsFilhos($user);
        if ($alunoIds === []) {
            return ['data' => [], 'meta' => ['total' => 0, 'escopo' => 'familia']];
        }

        try {
            $rows = DB::table('busca_ativa.processo as pr')
                ->leftJoin('pmieducar.aluno as al', 'al.cod_aluno', '=', 'pr.ref_cod_aluno')
                ->leftJoin('cadastro.pessoa as p', 'p.idpes', '=', 'al.ref_idpes')
                ->whereIn('pr.ref_cod_aluno', $alunoIds)
                ->where('pr.status', 'aberto')
                ->orderByDesc('pr.aberto_em')
                ->limit(100)
                ->get([
                    'pr.id',
                    'pr.ref_cod_aluno',
                    'pr.etapa_atual',
                    'pr.status',
                    'pr.aberto_em',
                    'pr.prazo_contato_em',
                    'p.nome as aluno',
                ]);

            $data = $rows->map(static fn ($r): array => [
                'id' => (int) $r->id,
                'aluno_id' => (int) $r->ref_cod_aluno,
                'aluno' => $r->aluno,
                'etapa_atual' => $r->etapa_atual,
                'status' => $r->status,
                'aberto_em' => $r->aberto_em,
                'prazo_contato_em' => $r->prazo_contato_em,
            ])->values()->all();

            return ['data' => $data, 'meta' => ['total' => count($data), 'escopo' => 'familia']];
        } catch (Throwable $e) {
            return ['data' => [], 'meta' => ['aviso' => $e->getMessage()]];
        }
    }

    /**
     * @return array{data:?array<string,mixed>,eventos:list<array<string,mixed>>,meta:array<string,mixed>}
     */
    public function processo(User $user, int $id): array
    {
        if (! Schema::hasTable('busca_ativa.processo')) {
            return ['data' => null, 'eventos' => [], 'meta' => ['status' => 503]];
        }

        try {
            $processo = DB::table('busca_ativa.processo')->where('id', $id)->first();
            if ($processo === null) {
                return ['data' => null, 'eventos' => [], 'meta' => ['status' => 404, 'erro' => 'nao_encontrado']];
            }
            if (! $this->familia->usuarioPodeVerAluno($user, (int) $processo->ref_cod_aluno)) {
                return ['data' => null, 'eventos' => [], 'meta' => ['status' => 403, 'erro' => 'aluno_nao_vinculado']];
            }

            $eventos = DB::table('busca_ativa.processo_evento')
                ->where('processo_id', $id)
                ->orderBy('created_at')
                ->get(['tipo', 'descricao', 'canal', 'created_at'])
                ->map(static fn ($e): array => [
                    'tipo' => $e->tipo,
                    'descricao' => $e->descricao,
                    'canal' => $e->canal,
                    'em' => $e->created_at,
                ])->values()->all();

            return [
                'data' => [
                    'id' => (int) $processo->id,
                    'aluno_id' => (int) $processo->ref_cod_aluno,
                    'etapa_atual' => $processo->etapa_atual,
                    'status' => $processo->status,
                    'aberto_em' => $processo->aberto_em,
                    'prazo_contato_em' => $processo->prazo_contato_em,
                    'observacoes' => $processo->observacoes,
                ],
                'eventos' => $eventos,
                'meta' => ['status' => 200, 'escopo' => 'familia'],
            ];
        } catch (Throwable $e) {
            return ['data' => null, 'eventos' => [], 'meta' => ['status' => 500, 'aviso' => $e->getMessage()]];
        }
    }

    /**
     * @return array{ok:bool,id?:int,meta:array<string,mixed>}
     */
    public function registrarAcompanhamento(User $user, int $id, array $dados): array
    {
        if (! Schema::hasTable('busca_ativa.familia_acompanhamento') || ! Schema::hasTable('busca_ativa.processo')) {
            return ['ok' => false, 'meta' => ['status' => 503]];
        }

        $processo = DB::table('busca_ativa.processo')->where('id', $id)->first();
        if ($processo === null) {
            return ['ok' => false, 'meta' => ['status' => 404]];
        }
        if (! $this->familia->usuarioPodeVerAluno($user, (int) $processo->ref_cod_aluno)) {
            return ['ok' => false, 'meta' => ['status' => 403, 'erro' => 'aluno_nao_vinculado']];
        }

        $aid = (int) DB::table('busca_ativa.familia_acompanhamento')->insertGetId([
            'processo_id' => $id,
            'ref_cod_aluno' => $processo->ref_cod_aluno,
            'canal' => (string) ($dados['canal'] ?? 'api'),
            'status_contato' => (string) ($dados['status_contato'] ?? 'realizado'),
            'retorno_familia' => $dados['retorno_familia'] ?? null,
            'compromisso_retorno_em' => $dados['compromisso_retorno_em'] ?? null,
            'registrado_por' => (string) ($dados['registrado_por'] ?? 'familia'),
            'payload' => json_encode($dados, JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (class_exists(\iEducar\Packages\BuritiBuscaAtiva\Services\ProcessoService::class)) {
            \iEducar\Packages\BuritiBuscaAtiva\Services\ProcessoService::registrarEvento(
                $id,
                'familia',
                (string) ($dados['retorno_familia'] ?? 'Acompanhamento familiar via Apps API'),
                (string) ($dados['canal'] ?? 'api'),
                ['familia_acompanhamento_id' => $aid, 'origem' => 'apps_api']
            );
            if (! empty($dados['etapa']) && class_exists(\iEducar\Packages\BuritiBuscaAtiva\Support\EtapasProcesso::class)) {
                $etapa = (string) $dados['etapa'];
                if (in_array($etapa, \iEducar\Packages\BuritiBuscaAtiva\Support\EtapasProcesso::todas(), true)) {
                    \iEducar\Packages\BuritiBuscaAtiva\Services\ProcessoService::atualizarEtapa($id, $etapa);
                }
            }
        }

        return ['ok' => true, 'id' => $aid, 'meta' => ['status' => 201]];
    }

    /**
     * @return array{data:list<array<string,mixed>>,meta:array<string,mixed>}
     */
    public function alertasAluno(User $user, int $alunoId): array
    {
        if (! $this->familia->usuarioPodeVerAluno($user, $alunoId)) {
            return ['data' => [], 'meta' => ['status' => 403, 'erro' => 'aluno_nao_vinculado']];
        }
        if (! Schema::hasTable('busca_ativa.alerta')) {
            return ['data' => [], 'meta' => ['status' => 503]];
        }

        $rows = DB::table('busca_ativa.alerta')
            ->where('ref_cod_aluno', $alunoId)
            ->orderByDesc('detectado_em')
            ->limit(50)
            ->get([
                'id', 'tipo', 'severidade', 'status', 'motivo',
                'faltas_consecutivas', 'frequencia_percentual', 'detectado_em',
            ]);

        return [
            'data' => $rows->map(static fn ($r): array => (array) $r)->values()->all(),
            'meta' => ['status' => 200, 'aluno_id' => $alunoId, 'escopo' => 'familia'],
        ];
    }

    /** @return list<int> */
    private function idsFilhos(User $user): array
    {
        return array_values(array_map(
            static fn (array $f): int => (int) $f['aluno_id'],
            $this->familia->filhos($user)
        ));
    }
}
