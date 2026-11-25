<?php

namespace iEducar\Packages\BuritiAppsApi\Http\Controllers;

use App\User;
use iEducar\Packages\BuritiAppsApi\Services\AppsGovernancaService;
use iEducar\Packages\BuritiAppsApi\Services\AppsUsuarioEscopoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppsAdminController
{
    public function __construct(
        private readonly AppsGovernancaService $gov,
        private readonly AppsUsuarioEscopoService $escopo,
    ) {}

    public function suspenderUsuario(Request $request, int $usuario): JsonResponse
    {
        $data = $request->validate([
            'motivo' => 'nullable|string|max:255',
        ]);

        /** @var User $ator */
        $ator = $request->user();
        if (! method_exists($ator, 'isAdmin') || ! $ator->isAdmin()) {
            return response()->json(['message' => 'Perfil admin exigido', 'code' => 'forbidden'], 403);
        }

        $user = User::query()->find($usuario);
        if ($user === null) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }
        if (! $this->escopo->podeGerenciar($ator, $user)) {
            return response()->json(['message' => 'Fora do escopo', 'code' => 'fora_do_escopo'], 403);
        }

        $result = $this->gov->suspenderUsuario($user, $data['motivo'] ?? null);

        return response()->json(['ok' => true] + $result);
    }

    public function reativarUsuario(Request $request, int $usuario): JsonResponse
    {
        /** @var User $ator */
        $ator = $request->user();
        if (! method_exists($ator, 'isAdmin') || ! $ator->isAdmin()) {
            return response()->json(['message' => 'Perfil admin exigido', 'code' => 'forbidden'], 403);
        }

        $user = User::query()->find($usuario);
        if ($user === null) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        return response()->json(['ok' => true] + $this->gov->reativarUsuario($user));
    }

    public function bloquearMunicipio(Request $request): JsonResponse
    {
        /** @var User $ator */
        $ator = $request->user();
        if (! method_exists($ator, 'isAdmin') || ! $ator->isAdmin()) {
            return response()->json(['message' => 'Somente admin bloqueia o município', 'code' => 'forbidden'], 403);
        }

        $data = $request->validate([
            'mensagem' => 'nullable|string|max:500',
        ]);

        return response()->json(['ok' => true] + $this->gov->bloquearMunicipio($data['mensagem'] ?? null));
    }

    public function liberarMunicipio(Request $request): JsonResponse
    {
        /** @var User $ator */
        $ator = $request->user();
        if (! method_exists($ator, 'isAdmin') || ! $ator->isAdmin()) {
            return response()->json(['message' => 'Somente admin libera o município', 'code' => 'forbidden'], 403);
        }

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
