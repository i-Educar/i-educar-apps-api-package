<?php

use iEducar\Packages\BuritiAppsApi\Http\Controllers\AppsCatalogoController;
use iEducar\Packages\BuritiAppsApi\Http\Controllers\AppsFamiliaController;
use iEducar\Packages\BuritiAppsApi\Http\Controllers\AppsProfessorController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/apps/v1')->group(function (): void {
    Route::get('catalogo', [AppsCatalogoController::class, 'index']);
    Route::get('exportacao/formatos', [AppsCatalogoController::class, 'formatos']);
    Route::get('diario/status', [AppsCatalogoController::class, 'diario']);

    Route::prefix('familia')->group(function (): void {
        Route::get('cardapio', [AppsFamiliaController::class, 'cardapio']);
        Route::get('transporte', [AppsFamiliaController::class, 'transporte']);
        Route::get('fila', [AppsFamiliaController::class, 'fila']);
        Route::get('filhos', [AppsFamiliaController::class, 'filhos']);
    });

    Route::prefix('professor')->group(function (): void {
        Route::get('turmas', [AppsProfessorController::class, 'turmas']);
        Route::get('historico-funcional', [AppsProfessorController::class, 'historico']);
        Route::post('sync', [AppsProfessorController::class, 'sync']);
    });
});
