<?php

namespace iEducar\Packages\BuritiAppsApi\Console;

use iEducar\Packages\BuritiAppsApi\Services\AppsGovernancaService;
use Illuminate\Console\Command;

class BloquearMunicipioCommand extends Command
{
    protected $signature = 'apps-api:bloquear-municipio {--mensagem=}';

    protected $description = 'Onda 5 — desliga apps-api + Suspender cliente i-Educar e revoga tokens apps';

    public function handle(AppsGovernancaService $gov): int
    {
        $r = $gov->bloquearMunicipio($this->option('mensagem') ?: null);
        $this->warn('Município bloqueado para apps/web (active_on_ieducar=0)');
        $this->line('  tokens_apps_revogados='.$r['tokens_apps_revogados']);
        $this->line('  '.$r['mensagem']);

        return self::SUCCESS;
    }
}
