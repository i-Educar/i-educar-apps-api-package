<?php

namespace iEducar\Packages\BuritiAppsApi\Http\Controllers;

use App\User;
use iEducar\Packages\BuritiAppsApi\Services\AppsConfigService;
use iEducar\Packages\BuritiAppsApi\Services\AppsGovernancaService;
use iEducar\Packages\BuritiAppsApi\Services\AppsMonitorService;
use iEducar\Packages\BuritiAppsApi\Services\AppsUsuarioEscopoService;
use iEducar\Packages\BuritiAppsApi\Support\AppsApiCatalogo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class AppsConfigWebController extends Controller
{
    public function index(AppsConfigService $config, AppsGovernancaService $gov, AppsMonitorService $monitor): View
    {
        $estado = $config->estadoAtual();
        $avisos = $config->avisosImpacto();
        $funcoes = $config->funcoesComStatus();
        $porGrupo = [];
        foreach ($funcoes as $f) {
            $porGrupo[$f['grupo']][] = $f;
        }

        return view('apps-api::config', [
            'estado' => $estado,
            'avisos' => $avisos,
            'featuresMeta' => AppsConfigService::FEATURES_META,
            'porGrupo' => $porGrupo,
            'cobertura' => AppsApiCatalogo::coberturaTr(),
            'govOk' => $gov->appsHabilitados(),
            'monitorResumo' => $monitor->resumo(),
            'diagnostico' => $config->diagnosticoEndpoints(),
        ]);
    }

    public function funcoes(AppsConfigService $config): View
    {
        $funcoes = $config->funcoesComStatus();
        $porGrupo = [];
        foreach ($funcoes as $f) {
            $porGrupo[$f['grupo']][] = $f;
        }

        return view('apps-api::funcoes', [
            'estado' => $config->estadoAtual(),
            'avisos' => $config->avisosImpacto(),
            'porGrupo' => $porGrupo,
            'featuresMeta' => AppsConfigService::FEATURES_META,
            'diagnostico' => $config->diagnosticoEndpoints(),
        ]);
    }

    public function governanca(
        Request $request,
        AppsConfigService $config,
        AppsGovernancaService $gov,
        AppsMonitorService $monitor,
        AppsUsuarioEscopoService $escopo,
    ): View {
        /** @var User|null $ator */
        $ator = $request->user();

        return view('apps-api::governanca', [
            'estado' => $config->estadoAtual(),
            'govOk' => $gov->appsHabilitados(),
            'resumo' => $monitor->resumo(),
            'tokens' => $monitor->tokensAtivos(),
            'suspensos' => $monitor->usuariosSuspensos(),
            'historico' => $monitor->historicoBloqueios(),
            'mensagem' => $gov->mensagemSuspensao(),
            'usuariosAtivos' => $escopo->usuariosSelecionaveis($ator, $request->query('q')),
            'buscaUsuarios' => (string) $request->query('q', ''),
            'postSuspender' => url('/apps-api/governanca/suspender'),
            'podeBloquearMunicipio' => $ator !== null && method_exists($ator, 'isAdmin') && $ator->isAdmin(),
        ]);
    }

    public function salvar(Request $request, AppsConfigService $config, AppsGovernancaService $gov): RedirectResponse
    {
        $features = [];
        foreach (array_keys(AppsConfigService::FEATURES_META) as $chave) {
            $features[$chave] = $request->boolean('features.'.$chave);
        }

        $antes = $config->estadoAtual();
        $estado = $config->salvar([
            'enabled' => $request->boolean('enabled'),
            'require_login' => $request->boolean('require_login'),
            'token_ttl_minutes' => (int) $request->input('token_ttl_minutes', 43200),
            'suspension_message' => (string) $request->input('suspension_message', ''),
            'features' => $features,
        ]);

        $gov->registrarEvento(
            'config_salva',
            'enabled='.($estado['enabled'] ? '1' : '0').' ttl='.$estado['token_ttl_minutes'],
            null,
            null,
            [
                'enabled' => $estado['enabled'],
                'require_login' => $estado['require_login'],
                'token_ttl_minutes' => $estado['token_ttl_minutes'],
            ]
        );

        $avisos = $config->avisosImpacto(
            (bool) $estado['enabled'],
            $estado['features']
        );
        $novos = count(array_filter($avisos, static fn ($a) => $a['nivel'] !== 'ok'));

        $msg = 'Configuração da Apps API salva.';
        if (! $estado['enabled'] && $antes['enabled']) {
            $msg .= ' API desligada — apps família/professor/admin recebem 503.';
        } elseif ($novos > 0) {
            $msg .= " {$novos} aviso(s) de funções indisponíveis — veja o painel.";
        }

        return redirect('/apps-api/configuracao')->with('ok', $msg);
    }

    public function previewImpacto(Request $request, AppsConfigService $config): View
    {
        $features = [];
        foreach (array_keys(AppsConfigService::FEATURES_META) as $chave) {
            $features[$chave] = $request->boolean('features.'.$chave);
        }
        $avisos = $config->avisosImpacto(
            $request->boolean('enabled'),
            $features
        );

        return view('apps-api::partials.avisos', [
            'avisos' => $avisos,
            'titulo' => 'Prévia de impacto',
        ]);
    }

    public function revogarToken(int $token, AppsGovernancaService $gov): RedirectResponse
    {
        $ok = $gov->revogarToken($token);

        return redirect('/apps-api/governanca')
            ->with($ok ? 'ok' : 'erro', $ok ? "Token #{$token} revogado." : 'Token não encontrado.');
    }

    public function suspenderUsuario(
        Request $request,
        AppsGovernancaService $gov,
        AppsUsuarioEscopoService $escopo,
    ): RedirectResponse {
        $data = $request->validate([
            'usuario_ids' => 'required|array|min:1',
            'usuario_ids.*' => 'integer|min:1',
            'motivo' => 'nullable|string|max:255',
        ]);

        /** @var User $ator */
        $ator = $request->user();
        $batch = $gov->suspenderUsuarios($data['usuario_ids'], $data['motivo'] ?? null, $ator);

        $redir = $request->input('_redirect', '/apps-api/governanca');

        if ($batch['ok'] === 0) {
            $motivo = $batch['falhas'][0]['erro'] ?? 'falha';

            return redirect($redir)->with('erro', 'Nenhum usuário suspenso ('.$motivo.').');
        }

        $msg = $batch['ok'].' usuário(s) suspenso(s): tokens e acesso apps/web bloqueados.';
        if ($batch['falhas'] !== []) {
            $msg .= ' '.count($batch['falhas']).' falha(s).';
        }

        return redirect($redir)->with('ok', $msg);
    }

    public function reativarUsuario(
        Request $request,
        int $usuario,
        AppsGovernancaService $gov,
        AppsUsuarioEscopoService $escopo,
    ): RedirectResponse {
        $user = User::query()->find($usuario);
        $redir = $request->input('_redirect', '/apps-api/governanca');
        if ($user === null) {
            return redirect($redir)->with('erro', 'Usuário não encontrado.');
        }

        /** @var User $ator */
        $ator = $request->user();
        if (! $ator->isAdmin() && ! $escopo->podeGerenciar($ator, $user)) {
            return redirect($redir)->with('erro', 'Sem permissão para reativar este usuário.');
        }

        $result = $gov->reativarUsuario($user);

        return redirect($redir)->with('ok', "Usuário {$result['login']} reativado.");
    }

    public function bloquearMunicipio(Request $request, AppsGovernancaService $gov): RedirectResponse
    {
        $data = $request->validate([
            'mensagem' => 'nullable|string|max:500',
        ]);

        $result = $gov->bloquearMunicipio($data['mensagem'] ?? null);

        return redirect('/apps-api/governanca')
            ->with('ok', 'Município bloqueado para apps · '.$result['tokens_apps_revogados'].' token(s) revogado(s).');
    }

    public function liberarMunicipio(AppsGovernancaService $gov): RedirectResponse
    {
        $gov->liberarMunicipio();

        return redirect('/apps-api/governanca')->with('ok', 'Município liberado para apps.');
    }
}
