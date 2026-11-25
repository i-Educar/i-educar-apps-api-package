<?php

namespace iEducar\Packages\BuritiAppsApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Governança de usuários (suspender/reativar/tokens):
 * admin, institucional ou escolar. Bloqueio de município continua só admin.
 */
final class ExigeGovernancaApps
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null) {
            abort(403, 'Apps API: autenticação obrigatória.');
        }

        $ok = (method_exists($user, 'isAdmin') && $user->isAdmin())
            || (method_exists($user, 'isInstitutional') && $user->isInstitutional())
            || (method_exists($user, 'isSchooling') && $user->isSchooling());

        if (! $ok) {
            abort(403, 'Apps API: governança restrita a admin, institucional ou escolar.');
        }

        return $next($request);
    }
}
