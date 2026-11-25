<?php

namespace iEducar\Packages\BuritiAppsApi\Http\Controllers;

use iEducar\Packages\BuritiAppsApi\Services\BuscaAtivaFamiliaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppsBuscaAtivaController
{
    public function processos(Request $request, BuscaAtivaFamiliaService $svc): JsonResponse
    {
        $result = $svc->processos($request->user());
        $status = (int) ($result['meta']['status'] ?? 200);

        return response()->json($result, $status === 503 ? 503 : 200);
    }

    public function processo(Request $request, int $id, BuscaAtivaFamiliaService $svc): JsonResponse
    {
        $result = $svc->processo($request->user(), $id);
        $status = (int) ($result['meta']['status'] ?? 200);

        return response()->json($result, in_array($status, [403, 404, 503], true) ? $status : 200);
    }

    public function acompanhamento(Request $request, int $id, BuscaAtivaFamiliaService $svc): JsonResponse
    {
        $dados = $request->validate([
            'canal' => 'nullable|string|max:30',
            'status_contato' => 'nullable|string|max:30',
            'retorno_familia' => 'nullable|string|max:2000',
            'compromisso_retorno_em' => 'nullable|date_format:Y-m-d',
            'registrado_por' => 'nullable|string|max:120',
            'etapa' => 'nullable|string|max:40',
        ]);

        $result = $svc->registrarAcompanhamento($request->user(), $id, $dados);
        $status = (int) ($result['meta']['status'] ?? 201);

        return response()->json($result, $status);
    }

    public function alertasAluno(Request $request, int $aluno, BuscaAtivaFamiliaService $svc): JsonResponse
    {
        $result = $svc->alertasAluno($request->user(), $aluno);
        $status = (int) ($result['meta']['status'] ?? 200);

        return response()->json($result, in_array($status, [403, 503], true) ? $status : 200);
    }
}
