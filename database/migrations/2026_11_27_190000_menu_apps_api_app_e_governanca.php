<?php

use App\Menu;
use App\Services\MenuCacheService;
use iEducar\Packages\BuritiAppsApi\Support\Processos;
use Illuminate\Database\Migrations\Migration;

/**
 * Unifica a identidade do menu: APIs → APP e Governança (/apps-api/configuracao).
 */
return new class extends Migration
{
    public function up(): void
    {
        Menu::query()
            ->where('process', Processos::RAIZ)
            ->update([
                'title' => 'APP e Governança',
                'description' => 'Configuração Sanctum, funções e governança dos apps',
                'link' => '/apps-api/configuracao',
                'icon' => 'fa-mobile',
                'active' => true,
            ]);

        // Títulos legados em qualquer nível
        Menu::query()
            ->where(function ($q): void {
                $q->where('title', 'APIs')
                    ->orWhere('title', 'Apps API')
                    ->orWhere('title', 'Governança apps');
            })
            ->where(function ($q): void {
                $q->where('link', 'like', '/apps-api%')
                    ->orWhere('process', Processos::RAIZ);
            })
            ->update(['title' => 'APP e Governança']);

        if (class_exists(MenuCacheService::class)) {
            try {
                app(MenuCacheService::class)->flushAll();
            } catch (\Throwable) {
            }
        }
    }

    public function down(): void
    {
        Menu::query()
            ->where('process', Processos::RAIZ)
            ->update([
                'title' => 'APIs',
                'description' => 'Configuração Sanctum e funções dos apps',
            ]);

        if (class_exists(MenuCacheService::class)) {
            try {
                app(MenuCacheService::class)->flushAll();
            } catch (\Throwable) {
            }
        }
    }
};
