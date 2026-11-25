<?php

namespace iEducar\Packages\BuritiAppsApi\Console;

use iEducar\Packages\BuritiAppsApi\Services\AppsGovernancaService;
use Illuminate\Console\Command;

class LiberarMunicipioCommand extends Command
{
    protected $signature = 'apps-api:liberar-municipio';

    protected $description = 'Onda 5 — reabilita apps-api e active_on_ieducar';

    public function handle(AppsGovernancaService $gov): int
    {
        $gov->liberarMunicipio();
        $this->info('Município liberado para apps/web');

        return self::SUCCESS;
    }
}
