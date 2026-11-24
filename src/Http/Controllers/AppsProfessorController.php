<?php

namespace iEducar\Packages\BuritiAppsApi\Http\Controllers;

use iEducar\Packages\BuritiAppsApi\Services\AppsApiFacade;
use iEducar\Packages\BuritiAppsApi\Services\ProfessorConsultaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppsProfessorController
{
    public function turmas(Request $request, ProfessorConsultaService $svc): JsonResponse
    {
        $ano = $request->query('ano');
        $data = $svc->turmas($request->user(), $ano !== null ? (int) $ano : null);

        return response()->json([
            'data' => $data,
            'meta' => [
                'fonte' => 'modules.professor_turma',
                'total' => count($data),
            ],
        ]);
    }

    public function historico(Request $request, AppsApiFacade $api): JsonResponse
    {
        $id = (int) $request->query('servidor_id', $request->user()->id);

        return response()->json($api->lotacaoResumo($id > 0 ? $id : (int) $request->user()->id));
    }

    public function sync(Request $request, ProfessorConsultaService $svc): JsonResponse
    {
        $eventos = $request->input('eventos', []);
        if (! is_array($eventos)) {
            return response()->json(['message' => 'eventos deve ser array'], 422);
        }

        $result = $svc->sync($eventos);

        return response()->json($result + [
            'idempotencia' => 'uuid por evento (cache 7d)',
            'status' => 'processed',
        ]);
    }

    public function chamada(Request $request, ProfessorConsultaService $svc): JsonResponse
    {
        $payload = $request->validate([
            'uuid' => 'required|string|max:64',
            'turma_id' => 'required|integer',
            'data' => 'required|date_format:Y-m-d',
            'lancamentos' => 'required|array|min:1',
            'lancamentos.*.aluno_id' => 'required|integer',
            'lancamentos.*.presente' => 'required|boolean',
        ]);

        $result = $svc->sync([[
            'uuid' => $payload['uuid'],
            'tipo' => 'chamada',
            'payload' => $payload,
        ]]);

        return response()->json([
            'ok' => ($result['aceitos'] ?? 0) > 0 || ($result['duplicados'] ?? 0) > 0,
            'sync' => $result,
            'meta' => ['destino' => 'i-Diário / modules.falta_* (fila)'],
        ], 202);
    }

    public function notas(Request $request, ProfessorConsultaService $svc): JsonResponse
    {
        $payload = $request->validate([
            'uuid' => 'required|string|max:64',
            'turma_id' => 'required|integer',
            'componente_id' => 'required|integer',
            'etapa' => 'required|string|max:8',
            'lancamentos' => 'required|array|min:1',
            'lancamentos.*.aluno_id' => 'required|integer',
            'lancamentos.*.nota' => 'required|numeric',
        ]);

        $result = $svc->sync([[
            'uuid' => $payload['uuid'],
            'tipo' => 'notas',
            'payload' => $payload,
        ]]);

        return response()->json([
            'ok' => ($result['aceitos'] ?? 0) > 0 || ($result['duplicados'] ?? 0) > 0,
            'sync' => $result,
            'meta' => ['destino' => 'i-Diário / modules.nota_* (fila)'],
        ], 202);
    }

    public function horario(AppsApiFacade $api): JsonResponse
    {
        return response()->json($api->horarioResumo());
    }

    public function eventos(AppsApiFacade $api): JsonResponse
    {
        return response()->json($api->eventosResumo());
    }
}
