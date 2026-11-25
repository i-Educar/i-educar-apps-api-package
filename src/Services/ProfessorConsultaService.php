<?php

namespace iEducar\Packages\BuritiAppsApi\Services;

use App\Models\LegacySchoolClassTeacher;
use App\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
     * Fila offline idempotente (uuid por evento) — persiste em apps_api quando schema existe.
     *
     * @param  list<array{uuid?:string,tipo?:string,payload?:mixed}>  $eventos
     * @param  int|null  $servidorId
     * @return array{aceitos:int,duplicados:int,rejeitados:int,itens:list<array<string,mixed>>,persistencia:string}
     */
    public function sync(array $eventos, ?int $servidorId = null): array
    {
        $aceitos = 0;
        $duplicados = 0;
        $rejeitados = 0;
        $itens = [];
        $persistencia = $this->hasSyncTable() ? 'apps_api.professor_sync_evento' : 'cache_memory';

        foreach ($eventos as $evento) {
            if (! is_array($evento)) {
                $rejeitados++;
                $itens[] = ['status' => 'rejeitado', 'motivo' => 'formato'];

                continue;
            }

            $uuid = (string) ($evento['uuid'] ?? '');
            $tipo = (string) ($evento['tipo'] ?? '');
            $payload = $evento['payload'] ?? $evento;

            if ($uuid === '' || $tipo === '') {
                $rejeitados++;
                $itens[] = ['uuid' => $uuid ?: null, 'status' => 'rejeitado', 'motivo' => 'uuid_ou_tipo'];

                continue;
            }

            $escopo = $this->validarEscopoEscrita($tipo, $payload, $servidorId);
            if ($escopo !== null) {
                $rejeitados++;
                $itens[] = ['uuid' => $uuid, 'tipo' => $tipo, 'status' => 'rejeitado', 'motivo' => $escopo];

                continue;
            }

            $key = 'apps-api:sync:'.$uuid;
            if ($this->syncSeen($key, $uuid)) {
                $duplicados++;
                $itens[] = ['uuid' => $uuid, 'tipo' => $tipo, 'status' => 'duplicado'];

                continue;
            }

            $this->syncRemember($key, $uuid, $tipo, $payload, $servidorId);
            $aceitos++;
            $itens[] = ['uuid' => $uuid, 'tipo' => $tipo, 'status' => 'aceito'];
        }

        return compact('aceitos', 'duplicados', 'rejeitados', 'itens', 'persistencia');
    }

    /**
     * Chamada/notas só na turma do próprio servidor. Outros tipos passam sem turma.
     *
     * @return string|null motivo de rejeição
     */
    private function validarEscopoEscrita(string $tipo, mixed $payload, ?int $servidorId): ?string
    {
        if (! in_array($tipo, ['chamada', 'notas'], true)) {
            return null;
        }

        $turmaId = (int) (is_array($payload) ? ($payload['turma_id'] ?? 0) : 0);
        if ($turmaId <= 0) {
            return 'turma_obrigatoria';
        }

        if ($servidorId === null || $servidorId <= 0) {
            return 'servidor_obrigatorio';
        }

        if (! $this->temTabelaProfessorTurma()) {
            // Ambiente de contrato / sem schema: aceita com turma_id presente
            return null;
        }

        if (! $this->servidorTemTurma($servidorId, $turmaId)) {
            return 'turma_fora_do_escopo';
        }

        return null;
    }

    public function servidorTemTurma(int $servidorId, int $turmaId, ?int $ano = null): bool
    {
        try {
            $q = LegacySchoolClassTeacher::query()
                ->where('servidor_id', $servidorId)
                ->where('turma_id', $turmaId);
            if ($ano !== null) {
                $q->where('ano', $ano);
            }

            return $q->exists();
        } catch (Throwable) {
            return false;
        }
    }

    private function temTabelaProfessorTurma(): bool
    {
        try {
            return Schema::hasTable('modules.professor_turma');
        } catch (Throwable) {
            return false;
        }
    }

    private function hasSyncTable(): bool
    {
        try {
            return Schema::hasTable('apps_api.professor_sync_evento');
        } catch (Throwable) {
            return false;
        }
    }

    private function syncSeen(string $key, string $uuid): bool
    {
        if (isset(self::$syncMemory[$key])) {
            return true;
        }

        if ($this->hasSyncTable()) {
            try {
                return DB::table('apps_api.professor_sync_evento')->where('uuid', $uuid)->exists();
            } catch (Throwable) {
                // fall through
            }
        }

        try {
            return Cache::has($key);
        } catch (Throwable) {
            return false;
        }
    }

    private function syncRemember(string $key, string $uuid, string $tipo, mixed $payload, ?int $servidorId): void
    {
        self::$syncMemory[$key] = true;

        if ($this->hasSyncTable()) {
            try {
                DB::table('apps_api.professor_sync_evento')->insert([
                    'uuid' => $uuid,
                    'tipo' => $tipo,
                    'servidor_id' => $servidorId,
                    'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                    'status' => 'aceito',
                    'created_at' => now(),
                ]);

                return;
            } catch (Throwable) {
                // fall through to cache
            }
        }

        try {
            Cache::put($key, ['tipo' => $tipo, 'at' => date('c')], 60 * 60 * 24 * 7);
        } catch (Throwable) {
            // standalone
        }
    }
}
