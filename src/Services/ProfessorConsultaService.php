<?php

namespace iEducar\Packages\BuritiAppsApi\Services;

use App\Models\LegacySchoolClassTeacher;
use App\User;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Consultas professor (5.14) — vínculo servidor-turma no núcleo.
 */
final class ProfessorConsultaService
{
    /** @var array<string, true> */
    private static array $syncMemory = [];

    /**
     * @return list<array<string, mixed>>
     */
    public function turmas(User $user, ?int $ano = null): array
    {
        try {
            $servidorId = (int) $user->id;
            $ano ??= (int) date('Y');

            $vinculos = LegacySchoolClassTeacher::query()
                ->with(['schoolClass:cod_turma,nm_turma,ref_ref_cod_escola,ano,max_aluno'])
                ->where('servidor_id', $servidorId)
                ->where('ano', $ano)
                ->limit(100)
                ->get();

            return $vinculos->map(static function (LegacySchoolClassTeacher $v): array {
                $turma = $v->schoolClass;

                return [
                    'vinculo_id' => $v->id,
                    'turma_id' => $v->turma_id,
                    'nome' => $turma?->nm_turma,
                    'escola_id' => $turma?->ref_ref_cod_escola,
                    'ano' => $v->ano,
                    'funcao_exercida' => $v->funcao_exercida,
                    'turno_id' => $v->turno_id,
                ];
            })->values()->all();
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Fila offline idempotente (uuid por evento).
     *
     * @param  list<array{uuid?:string,tipo?:string,payload?:mixed}>  $eventos
     * @return array{aceitos:int,duplicados:int,rejeitados:int,itens:list<array<string,mixed>>}
     */
    public function sync(array $eventos): array
    {
        $aceitos = 0;
        $duplicados = 0;
        $rejeitados = 0;
        $itens = [];

        foreach ($eventos as $evento) {
            if (! is_array($evento)) {
                $rejeitados++;
                $itens[] = ['status' => 'rejeitado', 'motivo' => 'formato'];

                continue;
            }

            $uuid = (string) ($evento['uuid'] ?? '');
            $tipo = (string) ($evento['tipo'] ?? '');

            if ($uuid === '' || $tipo === '') {
                $rejeitados++;
                $itens[] = ['uuid' => $uuid ?: null, 'status' => 'rejeitado', 'motivo' => 'uuid_ou_tipo'];

                continue;
            }

            $key = 'apps-api:sync:'.$uuid;
            if ($this->syncSeen($key)) {
                $duplicados++;
                $itens[] = ['uuid' => $uuid, 'tipo' => $tipo, 'status' => 'duplicado'];

                continue;
            }

            $this->syncRemember($key, $tipo);
            $aceitos++;
            $itens[] = ['uuid' => $uuid, 'tipo' => $tipo, 'status' => 'aceito'];
        }

        return compact('aceitos', 'duplicados', 'rejeitados', 'itens');
    }

    private function syncSeen(string $key): bool
    {
        if (isset(self::$syncMemory[$key])) {
            return true;
        }

        try {
            return Cache::has($key);
        } catch (Throwable) {
            return false;
        }
    }

    private function syncRemember(string $key, string $tipo): void
    {
        self::$syncMemory[$key] = true;

        try {
            Cache::put($key, ['tipo' => $tipo, 'at' => date('c')], 60 * 60 * 24 * 7);
        } catch (Throwable) {
            // standalone / sem cache store
        }
    }
}
