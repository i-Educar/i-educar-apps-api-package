<?php

use App\Menu;
use App\Services\MenuCacheService;
use iEducar\Packages\BuritiAppsApi\Support\Processos;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! \App\Support\Tenancy\PackageTenant::allows('buriti/i-educar-apps-api-package')) {
            return;
        }

        $configuracoes = Menu::query()
            ->whereNull('parent_id')
            ->where(function ($q) {
                $q->where('process', 25)->orWhere('title', 'Configurações');
            })
            ->first();

        if ($configuracoes === null) {
            return;
        }

        $raiz = Menu::query()->updateOrCreate(['process' => Processos::RAIZ], [
            'parent_id' => $configuracoes->getKey(),
            'title' => 'APP e Governança',
            'description' => 'Configuração Sanctum e funções dos apps',
            'link' => '/apps-api/configuracao',
            'icon' => 'fa-mobile',
            'order' => 4,
            'type' => 2,
            'active' => true,
        ]);

        $grupoIds = Menu::query()
            ->where('parent_id', $raiz->getKey())
            ->where('title', 'Governança')
            ->whereNull('process')
            ->pluck('id');

        $links = [
            [Processos::CONFIG, 'Configuração', '/apps-api/configuracao', 'Auth + funções + avisos', 1],
            [Processos::FUNCOES, 'Funções disponíveis', '/apps-api/funcoes', 'Catálogo com status', 2],
        ];

        foreach ($links as [$processo, $titulo, $link, $descricao, $ordem]) {
            Menu::query()->updateOrCreate(['process' => $processo], [
                'parent_id' => $raiz->getKey(),
                'title' => $titulo,
                'description' => $descricao,
                'link' => $link,
                'order' => $ordem,
                'type' => 3,
                'active' => true,
                'icon' => null,
            ]);
        }

        if ($grupoIds->isNotEmpty()) {
            Menu::query()->whereIn('id', $grupoIds)->delete();
        }

        $processos = [Processos::RAIZ, Processos::CONFIG, Processos::FUNCOES];
        $query = DB::table('pmieducar.tipo_usuario')->where('ativo', 1);
        try {
            if (Schema::hasColumn('pmieducar.tipo_usuario', 'nivel')) {
                $query->where('nivel', 1);
            }
        } catch (\Throwable) {
            //
        }
        $tipos = $query->get(['cod_tipo_usuario']);
        if ($tipos->isEmpty()) {
            $tipos = DB::table('pmieducar.tipo_usuario')->where('ativo', 1)->limit(1)->get(['cod_tipo_usuario']);
        }

        foreach ($tipos as $tipo) {
            foreach ($processos as $processo) {
                $menuId = Menu::query()->where('process', $processo)->value('id');
                if ($menuId === null) {
                    continue;
                }
                DB::table('pmieducar.menu_tipo_usuario')->updateOrInsert(
                    ['ref_cod_tipo_usuario' => $tipo->cod_tipo_usuario, 'menu_id' => $menuId],
                    ['cadastra' => 1, 'visualiza' => 1, 'exclui' => 0],
                );
            }
        }

        if (class_exists(MenuCacheService::class)) {
            app(MenuCacheService::class)->flushAll();
        }
    }

    public function down(): void
    {
        $raiz = Menu::query()->where('process', Processos::RAIZ)->first();
        if ($raiz === null) {
            return;
        }
        $folhas = Menu::query()->where('parent_id', $raiz->getKey())->pluck('id');
        $ids = $folhas->push($raiz->getKey());
        DB::table('pmieducar.menu_tipo_usuario')->whereIn('menu_id', $ids)->delete();
        Menu::query()->whereIn('id', $folhas)->delete();
        $raiz->delete();
        if (class_exists(MenuCacheService::class)) {
            app(MenuCacheService::class)->flushAll();
        }
    }
};
