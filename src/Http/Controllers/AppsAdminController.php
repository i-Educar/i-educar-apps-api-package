<?php

namespace iEducar\Packages\BuritiAppsApi\Http\Controllers;

use App\User;
use iEducar\Packages\BuritiAppsApi\Services\AppsGovernancaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppsAdminController
{
    public function __construct(private readonly AppsGovernancaService $gov) {}

    public function suspenderUsuario(Request $request, int $usuario): JsonResponse
    {
        $data = $request->validate([
            'motivo' => 'nullable|string|max:255',
        ]);

        $user = User::query()->find($usuario);
        if ($user === null) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        $result = $this->gov->suspenderUsuario($user, $data['motivo'] ?? null);

        return response()->json(['ok' => true] + $result);
    }

    public function reativarUsuario(int $usuario): JsonResponse
    {
        $user = User::query()->find($usuario);
        if ($user === null) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        return response()->json(['ok' => true] + $this->gov->reativarUsuario($user));
    }

    public function bloquearMunicipio(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mensagem' => 'nullable|string|max:500',
        ]);

        return response()->json(['ok' => true] + $this->gov->bloquearMunicipio($data['mensagem'] ?? null));
    }

    public function liberarMunicipio(): JsonResponse
    {
        return response()->json(['ok' => true] + $this->gov->liberarMunicipio());
    }

    public function statusGovernanca(): JsonResponse
    {
        return response()->json([
            'apps_habilitados' => $this->gov->appsHabilitados(),
            'mensagem' => $this->gov->mensagemSuspensao(),
            'require_login' => (bool) config('apps-api.require_login', true),
        ]);
    }
}
