<?php

namespace iEducar\Packages\BuritiAppsApi\Providers;

use iEducar\Packages\BuritiAppsApi\Http\Middleware\EnsureAppsPerfil;
use iEducar\Packages\BuritiAppsApi\Services\AppsApiFacade;
use iEducar\Packages\BuritiAppsApi\Services\FamiliaConsultaService;
use iEducar\Packages\BuritiAppsApi\Services\ProfessorConsultaService;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class AppsApiServiceProvider extends ServiceProvider
{
    public const string PACKAGE = 'buriti/i-educar-apps-api-package';

    public function register(): void
    {
        $this->app->singleton(AppsApiFacade::class);
        $this->app->singleton(FamiliaConsultaService::class);
        $this->app->singleton(ProfessorConsultaService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(dirname(__DIR__, 2).'/routes/api.php');

        /** @var Router $router */
        $router = $this->app->make('router');
        $router->aliasMiddleware('apps.perfil', EnsureAppsPerfil::class);

        $openapi = dirname(__DIR__, 2).'/docs/openapi-apps-v1.yaml';
        if (is_file($openapi)) {
            $this->publishes([
                $openapi => base_path('docs/apps-api/openapi-apps-v1.yaml'),
            ], 'apps-api-openapi');
        }
    }
}
