<?php

namespace iEducar\Packages\BuritiAppsApi\Http\Middleware;

use Closure;
use iEducar\Packages\BuritiAppsApi\Services\AppsGovernancaService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garante que o token Sanctum pertence a usuário ativo; caso contrário revoga o token atual.
 */
class EnsureAppsUserActive
{
    public function __construct(private readonly AppsGovernancaService $gov) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null) {
            return response()->json(['message' => 'Não autenticado', 'code' => 'unauthenticated'], 401);
        }

        if ($this->gov->usuarioPodeUsarApp($user)) {
            return $next($request);
        }

        if (method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Usuário suspenso ou inativo. Faça login novamente após liberação.',
            'code' => 'user_suspended',
        ], 403);
    }
}
