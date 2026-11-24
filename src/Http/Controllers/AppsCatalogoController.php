<?php

namespace iEducar\Packages\BuritiAppsApi\Http\Controllers;

use iEducar\Packages\BuritiAppsApi\Services\AppsApiFacade;
use iEducar\Packages\BuritiAppsApi\Support\AppsApiCatalogo;
use Illuminate\Http\JsonResponse;

class AppsCatalogoController
{
    public function index(AppsApiFacade $api): JsonResponse
    {
        return response()->json($api->catalogo());
    }

    public function formatos(): JsonResponse
    {
        return response()->json(['formatos' => AppsApiCatalogo::formatosExportacao()]);
    }

    public function diario(AppsApiFacade $api): JsonResponse
    {
        return response()->json($api->diarioStatus());
    }
}
