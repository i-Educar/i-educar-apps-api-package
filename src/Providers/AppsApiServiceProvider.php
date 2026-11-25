<?php

namespace iEducar\Packages\BuritiAppsApi\Providers;

use App\Support\PublicModuleRegistry;
use iEducar\Packages\BuritiAppsApi\Console\BloquearMunicipioCommand;
use iEducar\Packages\BuritiAppsApi\Console\LiberarMunicipioCommand;
use iEducar\Packages\BuritiAppsApi\Console\ReativarUsuarioCommand;
use iEducar\Packages\BuritiAppsApi\Console\SuspenderUsuarioCommand;
use iEducar\Packages\BuritiAppsApi\Http\Middleware\EnsureAppsApiEnabled;
use iEducar\Packages\BuritiAppsApi\Http\Middleware\EnsureAppsFeature;
use iEducar\Packages\BuritiAppsApi\Http\Middleware\EnsureAppsPerfil;
use iEducar\Packages\BuritiAppsApi\Http\Middleware\EnsureAppsUserActive;
use iEducar\Packages\BuritiAppsApi\Services\AppsApiFacade;
use iEducar\Packages\BuritiAppsApi\Services\AppsConfigService;
use iEducar\Packages\BuritiAppsApi\Services\AppsGovernancaService;
use iEducar\Packages\BuritiAppsApi\Services\AppsMonitorService;
use iEducar\Packages\BuritiAppsApi\Services\FamiliaConsultaService;
use iEducar\Packages\BuritiAppsApi\Services\ProfessorConsultaService;
use iEducar\Packages\BuritiAppsApi\Support\Identidade;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppsApiServiceProvider extends ServiceProvider
{
    public const string PACKAGE = 'buriti/i-educar-apps-api-package';

    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__, 2).'/config/apps-api.php', 'apps-api');

        $this->app->singleton(AppsApiFacade::class);
        $this->app->singleton(AppsGovernancaService::class);
        $this->app->singleton(AppsConfigService::class);
        $this->app->singleton(AppsMonitorService::class);
        $this->app->singleton(FamiliaConsultaService::class);
        $this->app->singleton(ProfessorConsultaService::class);
    }

    public function boot(): void
    {
        if (class_exists(\iEducar\Packages\BuritiLagunaAmbiente\Support\BuritiUi::class)) {
            \iEducar\Packages\BuritiLagunaAmbiente\Support\BuritiUi::registrar();
        }
        $root = dirname(__DIR__, 2);
        $this->loadRoutesFrom($root.'/routes/api.php');
        $this->loadViewsFrom($root.'/resources/views', 'apps-api');

        /** @var Router $router */
        $router = $this->app->make('router');
        $router->aliasMiddleware('apps.perfil', EnsureAppsPerfil::class);
        $router->aliasMiddleware('apps.enabled', EnsureAppsApiEnabled::class);
        $router->aliasMiddleware('apps.ativo', EnsureAppsUserActive::class);
        $router->aliasMiddleware('apps.feature', EnsureAppsFeature::class);

        // Carrega overrides persistidos (settings) sobre o config/env.
        try {
            $this->app->make(AppsConfigService::class)->carregarPersistido();
        } catch (\Throwable) {
            //
        }

        PublicModuleRegistry::register('apps-api', [
            'package' => self::PACKAGE,
            'titulo' => (string) config('apps-api.public.titulo', 'Apps API'),
            'descricao' => 'API Sanctum família/professor — configuração SME',
            'url' => config('apps-api.public.url') ?: url('/apps-api/configuracao'),
            'icone' => Identidade::ICONE,
            'css_class' => 'public-module',
            'aparecer_na_home' => (bool) config('apps-api.public.aparecer_na_home', false),
            'ordem' => (int) config('apps-api.public.ordem', 40),
        ]);

        Route::middleware([
            'web',
            'ieducar.navigation',
            'ieducar.footer',
            'ieducar.xssbypass',
            'ieducar.suspended',
            'auth',
            'ieducar.checkresetpassword',
        ])->group($root.'/routes/web.php');

        View::composer('apps-api::*', function ($view): void {
            $view->with('identidade', Identidade::tokens());
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                SuspenderUsuarioCommand::class,
                ReativarUsuarioCommand::class,
                BloquearMunicipioCommand::class,
                LiberarMunicipioCommand::class,
            ]);

            if (\App\Support\Tenancy\PackageTenant::allows(self::PACKAGE)) {
                $this->loadMigrationsFrom($root.'/database/migrations');
            }
        }

        $this->publishes([
            $root.'/config/apps-api.php' => config_path('apps-api.php'),
        ], 'apps-api-config');

        $openapi = $root.'/docs/openapi-apps-v1.yaml';
        if (is_file($openapi)) {
            $this->publishes([
                $openapi => base_path('docs/apps-api/openapi-apps-v1.yaml'),
            ], 'apps-api-openapi');
        }
    }
}
