<?php

namespace iEducar\Packages\BuritiAppsApi\Http\Controllers;

use iEducar\Packages\BuritiAppsApi\Services\AppsApiFacade;
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

    public function filhos(): JsonResponse
    {
        return response()->json([
            'data' => [],
            'meta' => ['fonte' => 'pmieducar.aluno + vínculo responsável', 'status' => 'contrato'],
        ]);
    }
}
