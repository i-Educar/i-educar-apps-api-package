<?php

namespace iEducar\Packages\BuritiAppsApi\Http\Middleware;

use Closure;
use iEducar\Packages\BuritiAppsApi\Services\AppsConfigService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloqueia endpoint cujo grupo/função foi desligado na configuração SME.
 */
final class EnsureAppsFeature
{
    public function __construct(private readonly AppsConfigService $config) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $rel = (string) $request->path();
        $rel = preg_replace('#^api/apps/v1/#', '', $rel) ?? $rel;
        $feature = $this->config->featureParaPath($rel);
        if ($feature === null || $this->config->featureAtiva($feature)) {
            return $next($request);
        }

        $meta = AppsConfigService::FEATURES_META[$feature] ?? null;
        $impacto = $meta['impacto'] ?? ['Função desabilitada na configuração da Apps API.'];

        return response()->json([
            'message' => ($meta['rotulo'] ?? $feature).' está desabilitada na configuração da SME.',
            'code' => 'apps_feature_disabled',
            'feature' => $feature,
            'indisponivel' => $impacto,
        ], 503);
    }
}
