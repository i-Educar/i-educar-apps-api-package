<?php

use iEducar\Packages\BuritiAppsApi\Http\Controllers\AppsAdminController;
use iEducar\Packages\BuritiAppsApi\Http\Controllers\AppsAuthController;
use iEducar\Packages\BuritiAppsApi\Http\Controllers\AppsBuscaAtivaController;
use iEducar\Packages\BuritiAppsApi\Http\Controllers\AppsCatalogoController;
use iEducar\Packages\BuritiAppsApi\Http\Controllers\AppsFamiliaController;
use iEducar\Packages\BuritiAppsApi\Http\Controllers\AppsProfessorController;
use iEducar\Packages\BuritiAppsApi\Http\Middleware\EnsureAppsApiEnabled;
use iEducar\Packages\BuritiAppsApi\Http\Middleware\EnsureAppsFeature;
use iEducar\Packages\BuritiAppsApi\Http\Middleware\EnsureAppsPerfil;
use iEducar\Packages\BuritiAppsApi\Http\Middleware\EnsureAppsUserActive;
use Illuminate\Support\Facades\Route;

Route::prefix('api/apps/v1')->group(function (): void {
    // Sempre públicos (mesmo com município bloqueado — app lê o status)
    Route::get('auth/status', [AppsAuthController::class, 'status']);
    Route::get('openapi.yaml', [AppsCatalogoController::class, 'openapi']);

    Route::middleware(EnsureAppsApiEnabled::class)->group(function (): void {
        // Rate limit: mitiga força bruta no login Sanctum dos apps
        Route::post('auth/login', [AppsAuthController::class, 'login'])
            ->middleware('throttle:10,1');

        Route::middleware(['auth:sanctum', EnsureAppsUserActive::class, EnsureAppsFeature::class])->group(function (): void {
            Route::post('auth/logout', [AppsAuthController::class, 'logout']);
            Route::post('auth/logout-all', [AppsAuthController::class, 'logoutAll']);
            Route::get('auth/me', [AppsAuthController::class, 'me']);

            Route::get('catalogo', [AppsCatalogoController::class, 'index']);
            Route::get('exportacao/formatos', [AppsCatalogoController::class, 'formatos']);
            Route::get('diario/status', [AppsCatalogoController::class, 'diario']);

            Route::middleware(EnsureAppsPerfil::class.':familia')->prefix('familia')->group(function (): void {
                Route::get('cardapio', [AppsFamiliaController::class, 'cardapio']);
                Route::get('transporte', [AppsFamiliaController::class, 'transporte']);
                Route::get('fila', [AppsFamiliaController::class, 'fila']);
                Route::get('filhos', [AppsFamiliaController::class, 'filhos']);
                Route::get('filhos/{aluno}/frequencia', [AppsFamiliaController::class, 'frequencia']);
                Route::get('filhos/{aluno}/notas', [AppsFamiliaController::class, 'notas']);
                Route::get('filhos/{aluno}/busca-ativa/alertas', [AppsBuscaAtivaController::class, 'alertasAluno']);
                Route::get('uniforme', [AppsFamiliaController::class, 'uniforme']);
                Route::get('atendimentos', [AppsFamiliaController::class, 'atendimentos']);
                Route::get('biblioteca', [AppsFamiliaController::class, 'biblioteca']);
                Route::get('busca-ativa/processos', [AppsBuscaAtivaController::class, 'processos']);
                Route::get('busca-ativa/processos/{id}', [AppsBuscaAtivaController::class, 'processo'])->whereNumber('id');
                Route::post('busca-ativa/processos/{id}/acompanhamento', [AppsBuscaAtivaController::class, 'acompanhamento'])->whereNumber('id');
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

            Route::middleware(EnsureAppsPerfil::class.':admin')->prefix('admin')->group(function (): void {
                Route::get('governanca', [AppsAdminController::class, 'statusGovernanca']);
                Route::post('usuarios/{usuario}/suspender', [AppsAdminController::class, 'suspenderUsuario']);
                Route::post('usuarios/{usuario}/reativar', [AppsAdminController::class, 'reativarUsuario']);
                Route::post('municipio/bloquear', [AppsAdminController::class, 'bloquearMunicipio']);
                Route::post('municipio/liberar', [AppsAdminController::class, 'liberarMunicipio']);
            });
        });
    });
});
