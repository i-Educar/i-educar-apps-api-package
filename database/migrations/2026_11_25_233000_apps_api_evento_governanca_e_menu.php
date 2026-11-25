<?php

use App\Menu;
use App\Services\MenuCacheService;
use iEducar\Packages\BuritiAppsApi\Support\Processos;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('apps_api.evento_governanca')) {
            Schema::create('apps_api.evento_governanca', function (Blueprint $table): void {
                $table->id();
                $table->string('acao', 40); // bloquear_municipio|liberar_municipio|suspender_usuario|reativar_usuario|revogar_token|config_salva
                $table->unsignedBigInteger('ator_id')->nullable();
                $table->string('ator_login', 120)->nullable();
                $table->unsignedBigInteger('alvo_usuario_id')->nullable();
                $table->string('alvo_login', 120)->nullable();
                $table->string('detalhe', 500)->nullable();
                $table->json('meta')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['acao', 'created_at']);
            });
        }

        $raiz = Menu::query()->where('process', Processos::RAIZ)->first();
        if ($raiz !== null) {
            Menu::query()->updateOrCreate(['process' => Processos::GOVERNANCA], [
                'parent_id' => $raiz->getKey(),
                'title' => 'Governança',
                'description' => 'Tokens, suspensões e bloqueios',
                'link' => '/apps-api/governanca',
                'order' => 3,
                'type' => 3,
                'active' => true,
            ]);

            $menuId = Menu::query()->where('process', Processos::GOVERNANCA)->value('id');
            if ($menuId !== null) {
                $tipos = DB::table('pmieducar.tipo_usuario')->where('ativo', 1)->get(['cod_tipo_usuario']);
                foreach ($tipos as $tipo) {
                    DB::table('pmieducar.menu_tipo_usuario')->updateOrInsert(
                        ['ref_cod_tipo_usuario' => $tipo->cod_tipo_usuario, 'menu_id' => $menuId],
                        ['cadastra' => 1, 'visualiza' => 1, 'exclui' => 0],
                    );
                }
            }
        }

        if (class_exists(MenuCacheService::class)) {
            app(MenuCacheService::class)->flushAll();
        }
    }

    public function down(): void
    {
        $menuId = Menu::query()->where('process', Processos::GOVERNANCA)->value('id');
        if ($menuId !== null) {
            DB::table('pmieducar.menu_tipo_usuario')->where('menu_id', $menuId)->delete();
            Menu::query()->where('id', $menuId)->delete();
        }
        Schema::dropIfExists('apps_api.evento_governanca');
    }
};
