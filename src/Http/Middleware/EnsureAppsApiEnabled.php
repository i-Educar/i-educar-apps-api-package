<?php

namespace iEducar\Packages\BuritiAppsApi\Http\Middleware;

use Closure;
use iEducar\Packages\BuritiAppsApi\Services\AppsGovernancaService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAppsApiEnabled
{
    public function __construct(private readonly AppsGovernancaService $gov) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->gov->appsHabilitados()) {
            return $next($request);
        }

        return response()->json([
            'message' => $this->gov->mensagemSuspensao(),
            'code' => 'apps_api_disabled',
            'apps_habilitados' => false,
        ], 503);
    }
}
