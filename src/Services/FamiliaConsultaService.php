<?php

namespace iEducar\Packages\BuritiAppsApi\Services;

use App\Models\LegacyDisciplineScore;
use App\Models\LegacyGeneralAbsence;
use App\Models\LegacyRegistration;
use App\Models\LegacyStudent;
use App\Models\LegacyStudentAbsence;
use App\User;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Consultas família (5.13) — Eloquent no núcleo quando disponível.
 */
final class FamiliaConsultaService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function filhos(User $user): array
    {
        try {
            $idpes = (int) $user->id;
            $alunos = LegacyStudent::query()
                ->with(['person:idpes,nome'])
                ->whereHas('individual', static function ($q) use ($idpes): void {
                    $q->where('idpes_responsavel', $idpes)
                        ->orWhere('idpes_mae', $idpes)
                        ->orWhere('idpes_pai', $idpes);
                })
                ->where('ativo', 1)
                ->limit(100)
                ->get(['cod_aluno', 'ref_idpes', 'tipo_responsavel']);

            return $alunos->map(static function (LegacyStudent $a): array {
                $matricula = null;
                try {
                    $matricula = $a->lastRegistration;
                } catch (Throwable) {
                    $matricula = null;
                }

                return [
                    'aluno_id' => $a->cod_aluno,
                    'pessoa_id' => $a->ref_idpes,
                    'nome' => $a->person?->name ?? $a->person?->nome ?? null,
                    'tipo_responsavel' => $a->tipo_responsavel,
                    'matricula_atual' => $matricula ? [
                        'id' => $matricula->cod_matricula ?? null,
                        'ano' => $matricula->ano ?? null,
                        'escola_id' => $matricula->ref_ref_cod_escola ?? null,
                        'curso_id' => $matricula->ref_cod_curso ?? null,
                        'serie_id' => $matricula->ref_ref_cod_serie ?? null,
                    ] : null,
                ];
            })->values()->all();
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * @return array{data: list<array<string, mixed>>, meta: array<string, mixed>}
     */
    public function frequencia(User $user, int $alunoId): array
    {
        if (! $this->usuarioPodeVerAluno($user, $alunoId)) {
            return ['data' => [], 'meta' => ['erro' => 'aluno_nao_vinculado', 'status' => 403]];
        }

        try {
            $matriculas = LegacyRegistration::query()
                ->where('ref_cod_aluno', $alunoId)
                ->where('ativo', 1)
                ->orderByDesc('ano')
                ->limit(3)
                ->pluck('cod_matricula');

            $faltas = [];
            foreach ($matriculas as $matriculaId) {
                $faltaAluno = LegacyStudentAbsence::query()
                    ->where('matricula_id', $matriculaId)
                    ->first();
                if ($faltaAluno === null) {
                    continue;
                }
                $itens = LegacyGeneralAbsence::query()
                    ->where('falta_aluno_id', $faltaAluno->id)
                    ->orderBy('etapa')
                    ->get(['etapa', 'quantidade']);
                foreach ($itens as $item) {
                    $faltas[] = [
                        'matricula_id' => (int) $matriculaId,
                        'etapa' => $item->etapa,
                        'quantidade' => $item->quantidade,
                        'tipo' => 'geral',
                    ];
                }
            }

            return [
                'data' => $faltas,
                'meta' => [
                    'fonte' => 'modules.falta_aluno / falta_geral',
                    'aluno_id' => $alunoId,
                    'nota' => 'Frequência detalhada por dia via i-Diário quando sincronizado',
                ],
            ];
        } catch (Throwable $e) {
            return [
                'data' => [],
                'meta' => ['fonte' => 'modules.falta_*', 'aviso' => $e->getMessage()],
            ];
        }
    }

    /**
     * @return array{data: list<array<string, mixed>>, meta: array<string, mixed>}
     */
    public function notas(User $user, int $alunoId): array
    {
        if (! $this->usuarioPodeVerAluno($user, $alunoId)) {
            return ['data' => [], 'meta' => ['erro' => 'aluno_nao_vinculado', 'status' => 403]];
        }

        try {
            $matricula = LegacyRegistration::query()
                ->where('ref_cod_aluno', $alunoId)
                ->where('ativo', 1)
                ->orderByDesc('ano')
                ->first();

            if ($matricula === null) {
                return ['data' => [], 'meta' => ['aluno_id' => $alunoId, 'aviso' => 'sem_matricula_ativa']];
            }

            $score = $matricula->studentScore;
            if ($score === null) {
                return ['data' => [], 'meta' => ['matricula_id' => $matricula->cod_matricula, 'aviso' => 'sem_notas']];
            }

            $notas = LegacyDisciplineScore::query()
                ->with('discipline:id,nome')
                ->where('nota_aluno_id', $score->id)
                ->orderBy('componente_curricular_id')
                ->orderBy('etapa')
                ->limit(200)
                ->get();

            $data = $notas->map(static fn (LegacyDisciplineScore $n): array => [
                'componente_id' => $n->componente_curricular_id,
                'componente' => $n->discipline?->nome ?? null,
                'etapa' => $n->etapa,
                'nota' => $n->nota,
                'nota_arredondada' => $n->nota_arredondada,
            ])->values()->all();

            return [
                'data' => $data,
                'meta' => [
                    'fonte' => 'modules.nota_componente_curricular',
                    'matricula_id' => $matricula->cod_matricula,
                    'ano' => $matricula->ano,
                ],
            ];
        } catch (Throwable $e) {
            return [
                'data' => [],
                'meta' => ['aviso' => $e->getMessage()],
            ];
        }
    }

    public function usuarioPodeVerAluno(User $user, int $alunoId): bool
    {
        try {
            $idpes = (int) $user->id;

            return LegacyStudent::query()
                ->where('cod_aluno', $alunoId)
                ->whereHas('individual', static function ($q) use ($idpes): void {
                    $q->where('idpes_responsavel', $idpes)
                        ->orWhere('idpes_mae', $idpes)
                        ->orWhere('idpes_pai', $idpes);
                })
                ->exists();
        } catch (Throwable) {
            // ambiente sem schema completo: permite leitura contratual
            return ! Schema::hasTable('pmieducar.aluno');
        }
    }
}
