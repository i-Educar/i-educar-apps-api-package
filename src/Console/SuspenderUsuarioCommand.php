<?php

namespace iEducar\Packages\BuritiAppsApi\Console;

use App\User;
use iEducar\Packages\BuritiAppsApi\Services\AppsGovernancaService;
use Illuminate\Console\Command;

class SuspenderUsuarioCommand extends Command
{
    protected $signature = 'apps-api:suspender-usuario {login : Matrícula/login do portal.funcionario} {--motivo=}';

    protected $description = 'Onda 5 — suspende usuário e revoga tokens Sanctum + sessões web';

    public function handle(AppsGovernancaService $gov): int
    {
        $login = (string) $this->argument('login');
        $user = User::query()->whereHas('employee', static fn ($q) => $q->where('matricula', $login))->first();
        if ($user === null) {
            $this->error("Usuário não encontrado: {$login}");

            return self::FAILURE;
        }

        $r = $gov->suspenderUsuario($user, $this->option('motivo') ?: null);
        $this->info("Suspenso #{$r['usuario_id']} ({$r['login']})");
        $this->line("  tokens_revogados={$r['tokens_revogados']} sessoes_web={$r['sessoes_web_revogadas']}");

        return self::SUCCESS;
    }
}
