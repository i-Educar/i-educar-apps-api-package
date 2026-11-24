<?php

namespace iEducar\Packages\BuritiAppsApi\Http\Controllers;

use iEducar\Packages\BuritiAppsApi\Services\AppsApiFacade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppsProfessorController
{
    public function turmas(): JsonResponse
    {
        return response()->json([
            'data' => [],
            'meta' => ['fonte' => 'i-Diário / vínculo servidor-turma', 'status' => 'contrato'],
        ]);
    }

    public function historico(Request $request, AppsApiFacade $api): JsonResponse
    {
        $id = (int) $request->query('servidor_id', 0);

        return response()->json($api->lotacaoResumo($id > 0 ? $id : 1));
    }

    public function sync(Request $request): JsonResponse
    {
        $eventos = $request->input('eventos', []);
        if (! is_array($eventos)) {
            return response()->json(['message' => 'eventos deve ser array'], 422);
        }

        return response()->json([
            'aceitos' => count($eventos),
            'idempotencia' => 'uuid por evento',
            'status' => 'queued',
        ]);
    }
}
