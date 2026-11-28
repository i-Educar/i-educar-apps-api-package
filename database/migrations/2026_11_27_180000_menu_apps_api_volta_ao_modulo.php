<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Desfaz o loop Configurações → Módulos ↔ /apps-api/configuracao:
 * o menu APIs volta a abrir a configuração dentro do módulo.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        DB::table('menus')
            ->where('link', '/configuracoes/modulos?modulo=apps-api')
            ->update([
                'link' => '/apps-api/configuracao',
                'description' => 'Configuração Sanctum, funções e governança dos apps',
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        DB::table('menus')
            ->where('link', '/apps-api/configuracao')
            ->update([
                'link' => '/configuracoes/modulos?modulo=apps-api',
                'description' => 'Flags da Apps API em Configurações → Módulos',
            ]);
    }
};
