<?php

namespace iEducar\Packages\BuritiAppsApi\Http\Controllers;

use iEducar\Packages\BuritiAppsApi\Services\AppsApiFacade;
use iEducar\Packages\BuritiAppsApi\Services\FamiliaConsultaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppsFamiliaController
{
    public function cardapio(Request $request, AppsApiFacade $api, FamiliaConsultaService $svc): JsonResponse
    {
        $data = (string) $request->query('data', date('Y-m-d'));
        $filhos = $svc->filhos($request->user());

        return response()->json($api->cardapioDemo($data) + [
            'escopo' => 'familia',
            'alunos_vinculados' => array_map(static fn (array $f): int => (int) $f['aluno_id'], $filhos),
        ]);
    }

    public function transporte(Request $request, AppsApiFacade $api, FamiliaConsultaService $svc): JsonResponse
    {
        // Escopo família: resumo geral + filhos vinculados (não vaza matrículas de outros)
        $filhos = $svc->filhos($request->user());

        return response()->json($api->transporteResumo() + [
            'escopo' => 'familia',
            'alunos_vinculados' => array_map(static fn (array $f): int => (int) $f['aluno_id'], $filhos),
        ]);
    }

    public function fila(Request $request, AppsApiFacade $api, FamiliaConsultaService $svc): JsonResponse
    {
        $filhos = $svc->filhos($request->user());

        return response()->json($api->filaResumo() + [
            'escopo' => 'familia',
            'alunos_vinculados' => array_map(static fn (array $f): int => (int) $f['aluno_id'], $filhos),
        ]);
    }

    public function filhos(Request $request, FamiliaConsultaService $svc): JsonResponse
    {
        $data = $svc->filhos($request->user());

        return response()->json([
            'data' => $data,
            'meta' => [
                'fonte' => 'pmieducar.aluno + cadastro.fisica (responsável/mãe/pai)',
                'total' => count($data),
            ],
        ]);
    }

    public function frequencia(Request $request, int $aluno, FamiliaConsultaService $svc): JsonResponse
    {
        $result = $svc->frequencia($request->user(), $aluno);
        $status = (int) ($result['meta']['status'] ?? 200);

        return response()->json($result, $status === 403 ? 403 : 200);
    }

    public function notas(Request $request, int $aluno, FamiliaConsultaService $svc): JsonResponse
    {
        $result = $svc->notas($request->user(), $aluno);
        $status = (int) ($result['meta']['status'] ?? 200);

        return response()->json($result, $status === 403 ? 403 : 200);
    }

    public function uniforme(Request $request, AppsApiFacade $api, FamiliaConsultaService $svc): JsonResponse
    {
        $filhos = $svc->filhos($request->user());

        return response()->json($api->uniformeResumo() + [
            'escopo' => 'familia',
            'alunos_vinculados' => array_map(static fn (array $f): int => (int) $f['aluno_id'], $filhos),
        ]);
    }

    public function atendimentos(Request $request, AppsApiFacade $api, FamiliaConsultaService $svc): JsonResponse
    {
        $filhos = $svc->filhos($request->user());

        return response()->json($api->atendimentosResumo() + [
            'escopo' => 'familia',
            'alunos_vinculados' => array_map(static fn (array $f): int => (int) $f['aluno_id'], $filhos),
        ]);
    }

    public function biblioteca(Request $request, AppsApiFacade $api, FamiliaConsultaService $svc): JsonResponse
    {
        $filhos = $svc->filhos($request->user());

        return response()->json($api->bibliotecaResumo() + [
            'escopo' => 'familia',
            'alunos_vinculados' => array_map(static fn (array $f): int => (int) $f['aluno_id'], $filhos),
        ]);
    }
}
