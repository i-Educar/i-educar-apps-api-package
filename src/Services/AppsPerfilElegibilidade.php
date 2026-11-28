<?php

namespace iEducar\Packages\BuritiAppsApi\Services;

use App\Models\LegacySchoolClassTeacher;
use App\Models\LegacyStudent;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Garante que o perfil pedido no login corresponde ao vínculo real do usuário
 * (responsável, professor ou admin) — evita token com ability indevida.
 */
final class AppsPerfilElegibilidade
{
    /**
     * @return array{ok:bool,code:?string,message:?string}
     */
    public function avaliar(User $user, string $perfil): array
    {
        return match ($perfil) {
            'admin' => $this->avaliarAdmin($user),
            'familia' => $this->avaliarFamilia($user),
            'professor' => $this->avaliarProfessor($user),
            'equipe' => $this->avaliarEquipe($user),
            default => ['ok' => false, 'code' => 'perfil_invalido', 'message' => 'Perfil inválido'],
        };
    }

    /**
     * Equipe técnica: servidor da rede (funcionário ativo) ou admin.
     * Cobre almoxarifado, biblioteca, ACT e operações com QR.
     *
     * @return array{ok:bool,code:?string,message:?string}
     */
    private function avaliarEquipe(User $user): array
    {
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return ['ok' => true, 'code' => null, 'message' => null];
        }

        try {
            if (Schema::hasTable('portal.funcionario')) {
                $ativo = DB::table('portal.funcionario')
                    ->where('ref_cod_pessoa_fj', (int) $user->id)
                    ->where('ativo', 1)
                    ->exists();
                if ($ativo) {
                    return ['ok' => true, 'code' => null, 'message' => null];
                }
            }
        } catch (Throwable) {
            return ['ok' => true, 'code' => null, 'message' => null];
        }

        // Sem schema portal: libera para homologação de contrato.
        try {
            if (! Schema::hasTable('portal.funcionario')) {
                return ['ok' => true, 'code' => null, 'message' => null];
            }
        } catch (Throwable) {
            return ['ok' => true, 'code' => null, 'message' => null];
        }

        return [
            'ok' => false,
            'code' => 'forbidden_perfil',
            'message' => 'Perfil equipe exige funcionário ativo da rede',
        ];
    }

    /** @return array{ok:bool,code:?string,message:?string} */
    private function avaliarAdmin(User $user): array
    {
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return ['ok' => true, 'code' => null, 'message' => null];
        }

        return [
            'ok' => false,
            'code' => 'forbidden_perfil',
            'message' => 'Perfil admin negado',
        ];
    }

    /**
     * Família: responsável/mãe/pai de ao menos um aluno ativo, ou admin (homologação).
     * Sem schema de aluno (ambiente de contrato): libera.
     *
     * @return array{ok:bool,code:?string,message:?string}
     */
    private function avaliarFamilia(User $user): array
    {
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return ['ok' => true, 'code' => null, 'message' => null];
        }

        try {
            if (! Schema::hasTable('pmieducar.aluno')) {
                return ['ok' => true, 'code' => null, 'message' => null];
            }
        } catch (Throwable) {
            return ['ok' => true, 'code' => null, 'message' => null];
        }

        $idpes = (int) $user->id;
        try {
            $vinculado = LegacyStudent::query()
                ->where('ativo', 1)
                ->whereHas('individual', static function ($q) use ($idpes): void {
                    $q->where('idpes_responsavel', $idpes)
                        ->orWhere('idpes_mae', $idpes)
                        ->orWhere('idpes_pai', $idpes);
                })
                ->exists();
        } catch (Throwable) {
            return [
                'ok' => false,
                'code' => 'perfil_familia_indisponivel',
                'message' => 'Não foi possível validar vínculo familiar',
            ];
        }

        if ($vinculado) {
            return ['ok' => true, 'code' => null, 'message' => null];
        }

        return [
            'ok' => false,
            'code' => 'forbidden_perfil',
            'message' => 'Perfil família exige vínculo como responsável, mãe ou pai de aluno ativo',
        ];
    }

    /**
     * Professor: vínculo em modules.professor_turma (qualquer ano) ou admin.
     * Sem tabela: libera (contrato).
     *
     * @return array{ok:bool,code:?string,message:?string}
     */
    private function avaliarProfessor(User $user): array
    {
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return ['ok' => true, 'code' => null, 'message' => null];
        }

        try {
            if (! Schema::hasTable('modules.professor_turma') && ! class_exists(LegacySchoolClassTeacher::class)) {
                return ['ok' => true, 'code' => null, 'message' => null];
            }
        } catch (Throwable) {
            return ['ok' => true, 'code' => null, 'message' => null];
        }

        $servidorId = (int) $user->id;
        try {
            $temTurma = LegacySchoolClassTeacher::query()
                ->where('servidor_id', $servidorId)
                ->limit(1)
                ->exists();
        } catch (Throwable) {
            // Sem Eloquent/schema: não bloqueia login do professor (writes ainda validam turma).
            return ['ok' => true, 'code' => null, 'message' => null];
        }

        if ($temTurma) {
            return ['ok' => true, 'code' => null, 'message' => null];
        }

        return [
            'ok' => false,
            'code' => 'forbidden_perfil',
            'message' => 'Perfil professor exige vínculo servidor–turma',
        ];
    }
}
