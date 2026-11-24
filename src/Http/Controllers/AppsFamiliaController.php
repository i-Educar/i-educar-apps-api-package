<?php

namespace iEducar\Packages\BuritiAppsApi\Http\Controllers;

use iEducar\Packages\BuritiAppsApi\Services\AppsApiFacade;
use iEducar\Packages\BuritiAppsApi\Services\FamiliaConsultaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppsFamiliaController
{
    public function cardapio(Request $request, AppsApiFacade $api): JsonResponse
    {
        $data = (string) $request->query('data', date('Y-m-d'));

        return response()->json($api->cardapioDemo($data));
    }

    public function transporte(AppsApiFacade $api): JsonResponse
    {
        return response()->json($api->transporteResumo());
    }

    public function fila(AppsApiFacade $api): JsonResponse
    {
        return response()->json($api->filaResumo());
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

    public function uniforme(AppsApiFacade $api): JsonResponse
    {
        return response()->json($api->uniformeResumo());
    }

    public function atendimentos(AppsApiFacade $api): JsonResponse
    {
        return response()->json($api->atendimentosResumo());
    }

    public function biblioteca(AppsApiFacade $api): JsonResponse
    {
        return response()->json($api->bibliotecaResumo());
    }
}
