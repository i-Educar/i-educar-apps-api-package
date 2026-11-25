<?php

use iEducar\Packages\BuritiAppsApi\Http\Controllers\AppsConfigWebController;
use iEducar\Packages\BuritiAppsApi\Http\Middleware\ExigeAdminApps;
use iEducar\Packages\BuritiAppsApi\Http\Middleware\ExigeGovernancaApps;
use Illuminate\Support\Facades\Route;

Route::middleware([ExigeAdminApps::class])->prefix('apps-api')->group(function (): void {
    Route::get('/configuracao', [AppsConfigWebController::class, 'index']);
    Route::post('/configuracao', [AppsConfigWebController::class, 'salvar']);
    Route::post('/configuracao/preview', [AppsConfigWebController::class, 'previewImpacto']);
    Route::get('/funcoes', [AppsConfigWebController::class, 'funcoes']);

    Route::post('/governanca/bloquear', [AppsConfigWebController::class, 'bloquearMunicipio']);
    Route::post('/governanca/liberar', [AppsConfigWebController::class, 'liberarMunicipio']);
});

Route::middleware([ExigeGovernancaApps::class])->prefix('apps-api')->group(function (): void {
    Route::get('/governanca', [AppsConfigWebController::class, 'governanca']);
    Route::post('/governanca/suspender', [AppsConfigWebController::class, 'suspenderUsuario']);
    Route::post('/governanca/usuarios/{usuario}/reativar', [AppsConfigWebController::class, 'reativarUsuario']);
    Route::post('/governanca/tokens/{token}/revogar', [AppsConfigWebController::class, 'revogarToken']);
});
