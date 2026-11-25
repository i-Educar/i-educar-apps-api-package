<?php

namespace iEducar\Packages\BuritiAppsApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Tela de configuração Apps API — somente administrador. */
final class ExigeAdminApps
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null || ! method_exists($user, 'isAdmin') || ! $user->isAdmin()) {
            abort(403, 'Apps API: configuração restrita a administradores.');
        }

        return $next($request);
    }
}
