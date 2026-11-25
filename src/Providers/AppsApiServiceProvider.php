<?php

namespace iEducar\Packages\BuritiAppsApi\Providers;

use iEducar\Packages\BuritiAppsApi\Console\BloquearMunicipioCommand;
use iEducar\Packages\BuritiAppsApi\Console\LiberarMunicipioCommand;
use iEducar\Packages\BuritiAppsApi\Console\ReativarUsuarioCommand;
use iEducar\Packages\BuritiAppsApi\Console\SuspenderUsuarioCommand;
use iEducar\Packages\BuritiAppsApi\Http\Middleware\EnsureAppsApiEnabled;
use iEducar\Packages\BuritiAppsApi\Http\Middleware\EnsureAppsPerfil;
use iEducar\Packages\BuritiAppsApi\Http\Middleware\EnsureAppsUserActive;
use iEducar\Packages\BuritiAppsApi\Services\AppsApiFacade;
use iEducar\Packages\BuritiAppsApi\Services\AppsGovernancaService;
use iEducar\Packages\BuritiAppsApi\Services\FamiliaConsultaService;
use iEducar\Packages\BuritiAppsApi\Services\ProfessorConsultaService;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class AppsApiServiceProvider extends ServiceProvider
{
    public const string PACKAGE = 'buriti/i-educar-apps-api-package';

    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__, 2).'/config/apps-api.php', 'apps-api');

        $this->app->singleton(AppsApiFacade::class);
        $this->app->singleton(AppsGovernancaService::class);
        $this->app->singleton(FamiliaConsultaService::class);
        $this->app->singleton(ProfessorConsultaService::class);
    }

    public function boot(): void
    {
        $root = dirname(__DIR__, 2);
        $this->loadRoutesFrom($root.'/routes/api.php');

        /** @var Router $router */
        $router = $this->app->make('router');
        $router->aliasMiddleware('apps.perfil', EnsureAppsPerfil::class);
        $router->aliasMiddleware('apps.enabled', EnsureAppsApiEnabled::class);
        $router->aliasMiddleware('apps.ativo', EnsureAppsUserActive::class);

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
