<?php

use iEducar\Packages\BuritiAppsApi\Http\Controllers\AppsAuthController;
use iEducar\Packages\BuritiAppsApi\Http\Controllers\AppsCatalogoController;
use iEducar\Packages\BuritiAppsApi\Http\Controllers\AppsFamiliaController;
use iEducar\Packages\BuritiAppsApi\Http\Controllers\AppsProfessorController;
use iEducar\Packages\BuritiAppsApi\Http\Middleware\EnsureAppsPerfil;
use Illuminate\Support\Facades\Route;

Route::prefix('api/apps/v1')->group(function (): void {
    // Públicos
    Route::get('catalogo', [AppsCatalogoController::class, 'index']);
    Route::get('openapi.yaml', [AppsCatalogoController::class, 'openapi']);
    Route::get('exportacao/formatos', [AppsCatalogoController::class, 'formatos']);
    Route::get('diario/status', [AppsCatalogoController::class, 'diario']);
    Route::post('auth/login', [AppsAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('auth/logout', [AppsAuthController::class, 'logout']);
        Route::get('auth/me', [AppsAuthController::class, 'me']);

        Route::middleware(EnsureAppsPerfil::class.':familia')->prefix('familia')->group(function (): void {
            Route::get('cardapio', [AppsFamiliaController::class, 'cardapio']);
            Route::get('transporte', [AppsFamiliaController::class, 'transporte']);
            Route::get('fila', [AppsFamiliaController::class, 'fila']);
            Route::get('filhos', [AppsFamiliaController::class, 'filhos']);
            Route::get('filhos/{aluno}/frequencia', [AppsFamiliaController::class, 'frequencia']);
            Route::get('filhos/{aluno}/notas', [AppsFamiliaController::class, 'notas']);
            Route::get('uniforme', [AppsFamiliaController::class, 'uniforme']);
            Route::get('atendimentos', [AppsFamiliaController::class, 'atendimentos']);
            Route::get('biblioteca', [AppsFamiliaController::class, 'biblioteca']);
        });

        Route::middleware(EnsureAppsPerfil::class.':professor')->prefix('professor')->group(function (): void {
            Route::get('turmas', [AppsProfessorController::class, 'turmas']);
            Route::get('historico-funcional', [AppsProfessorController::class, 'historico']);
            Route::post('sync', [AppsProfessorController::class, 'sync']);
            Route::post('chamada', [AppsProfessorController::class, 'chamada']);
            Route::post('notas', [AppsProfessorController::class, 'notas']);
            Route::get('horario', [AppsProfessorController::class, 'horario']);
            Route::get('eventos', [AppsProfessorController::class, 'eventos']);
        });
    });
});
