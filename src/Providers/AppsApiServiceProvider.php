<?php

namespace iEducar\Packages\BuritiAppsApi\Providers;

use iEducar\Packages\BuritiAppsApi\Services\AppsApiFacade;
use Illuminate\Support\ServiceProvider;

class AppsApiServiceProvider extends ServiceProvider
{
    public const string PACKAGE = 'buriti/i-educar-apps-api-package';

    public function register(): void
    {
        $this->app->singleton(AppsApiFacade::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(dirname(__DIR__, 2).'/routes/api.php');
    }
}
