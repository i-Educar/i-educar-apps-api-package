<?php

namespace iEducar\Packages\BuritiAppsApi\Console;

use App\User;
use iEducar\Packages\BuritiAppsApi\Services\AppsGovernancaService;
use Illuminate\Console\Command;

class ReativarUsuarioCommand extends Command
{
    protected $signature = 'apps-api:reativar-usuario {login}';

    protected $description = 'Onda 5 — reativa usuário suspenso';

    public function handle(AppsGovernancaService $gov): int
    {
        $login = (string) $this->argument('login');
        $user = User::query()->whereHas('employee', static fn ($q) => $q->where('matricula', $login))->first();
        if ($user === null) {
            $this->error("Usuário não encontrado: {$login}");

            return self::FAILURE;
        }

        $r = $gov->reativarUsuario($user);
        $this->info("Reativado #{$r['usuario_id']} ({$r['login']})");

        return self::SUCCESS;
    }
}
