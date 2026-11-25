<?php

namespace iEducar\Packages\BuritiAppsApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAppsPerfil
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$perfis): Response
    {
        $user = $request->user();
        if ($user === null) {
            return response()->json(['message' => 'Não autenticado'], 401);
        }

        $abilities = [];
        if (method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
            $abilities = $user->currentAccessToken()->abilities ?? [];
        }

        foreach ($perfis as $perfil) {
            if (in_array($perfil, $abilities, true) || in_array('*', $abilities, true)) {
                return $next($request);
            }
        }

        return response()->json([
            'message' => 'Perfil insuficiente',
            'requer' => $perfis,
        ], 403);
    }
}
