<?php

namespace iEducar\Packages\BuritiAppsApi\Http\Controllers;

use iEducar\Packages\BuritiAppsApi\Services\AppsApiFacade;
use Illuminate\Http\JsonResponse;

class AppsCatalogoController
{
    public function index(AppsApiFacade $api): JsonResponse
    {
        return response()->json($api->catalogo());
    }

    public function formatos(AppsApiFacade $api): JsonResponse
    {
        $cat = $api->catalogo();

        return response()->json([
            'formatos' => $cat['exportacao_formatos'],
        ]);
    }

    public function diario(AppsApiFacade $api): JsonResponse
    {
        return response()->json($api->diarioStatus());
    }

    public function openapi(): JsonResponse
    {
        $path = dirname(__DIR__, 3).'/docs/openapi-apps-v1.yaml';
        if (! is_file($path)) {
            return response()->json(['message' => 'OpenAPI não encontrado'], 404);
        }

        return response()->json([
            'format' => 'openapi-3.0',
            'path' => 'docs/openapi-apps-v1.yaml',
            'url_relativa' => '/api/apps/v1/openapi.yaml',
            'bytes' => filesize($path),
        ]);
    }
}
