@extends('apps-api::layout')

@section('titulo', 'Governança — Apps da rede')
@section('migalha', 'Governança')
@section('icone-tela', 'fa-shield')
@section('titulo-tela', 'Governar acessos')
@section('lead')
    Bloqueie o município, desative pessoas, revogue tokens e acompanhe o histórico — com linguagem clara para a SME.
@endsection

@section('acoes-topo')
    @if($govOk)
        <span class="badge ok"><i class="fa fa-check"></i> Apps liberados</span>
    @else
        <span class="badge fail"><i class="fa fa-ban"></i> Apps suspensos</span>
    @endif
    <a class="botao claro icone" href="{{ url('/apps-api/configuracao') }}" title="Configuração" aria-label="Configuração"><i aria-hidden="true" class="fa fa-sliders"></i></a>
@endsection

@section('conteudo')

    <div class="hero-status {{ $govOk ? '' : 'suspenso' }}">
        <div class="lado">
            <span class="bolha"><i class="fa {{ $govOk ? 'fa-shield' : 'fa-ban' }}"></i></span>
            <div>
                <h2>{{ $govOk ? 'Município liberado para os apps' : 'Município com apps suspensos' }}</h2>
                <p>
                    @if($govOk)
                        Tokens ativos: {{ $resumo['tokens'] }} · pessoas suspensas: {{ $resumo['suspensos'] }}.
                    @else
                        Ninguém autentica nos aplicativos até liberar novamente.
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div class="numeros">
        <article style="--tom: {{ $resumo['apps_on'] ? 'var(--ok)' : 'var(--erro)' }}">
            <span class="kpi-ico"><i class="fa fa-mobile"></i></span>
            <span>Apps habilitados</span>
            <strong>{{ $resumo['apps_on'] ? 'Sim' : 'Não' }}</strong>
        </article>
        <article>
            <span class="kpi-ico"><i class="fa fa-key"></i></span>
            <span>Tokens ativos</span>
            <strong>{{ $resumo['tokens'] }}</strong>
        </article>
        <article style="--tom: {{ ($resumo['suspensos'] ?? 0) > 0 ? 'var(--ambar)' : 'var(--cor)' }}">
            <span class="kpi-ico"><i class="fa fa-user-times"></i></span>
            <span>Usuários suspensos</span>
            <strong>{{ $resumo['suspensos'] }}</strong>
        </article>
        <article>
            <span class="kpi-ico"><i class="fa fa-history"></i></span>
            <span>Eventos</span>
            <strong>{{ $resumo['eventos'] }}</strong>
        </article>
        <article>
            <span class="kpi-ico"><i class="fa fa-clock-o"></i></span>
            <span>TTL token</span>
            <strong>{{ $estado['token_ttl_minutes'] }} min</strong>
        </article>
        <article style="--tom: {{ $estado['enabled'] ? 'var(--ok)' : 'var(--erro)' }}">
            <span class="kpi-ico"><i class="fa fa-power-off"></i></span>
            <span>API master</span>
            <strong>{{ $estado['enabled'] ? 'Ligada' : 'Desligada' }}</strong>
        </article>
    </div>

    @if(!empty($podeBloquearMunicipio))
    <div class="bloco passo">
        <h2><span class="passo-tag">A</span> Município</h2>
        <p class="sub">Mensagem atual mostrada nos apps: <em>{{ $mensagem }}</em></p>
        <div class="form-grid">
            @if($govOk)
                <form method="post" action="{{ url('/apps-api/governanca/bloquear') }}" class="col-wide" style="display:grid;gap:10px;grid-template-columns:1fr auto;align-items:end">
                    @csrf
                    <label>Aviso que as pessoas verão no celular
                        <textarea name="mensagem" rows="2" placeholder="{{ $mensagem }}"></textarea>
                        <small style="font-weight:500;color:var(--suave)">Como um aviso no WhatsApp: texto curto e claro.</small>
                    </label>
                    <button class="botao perigo" type="submit" onclick="return confirm('Bloquear apps no município e revogar todos os tokens?')">
                        <i class="fa fa-ban"></i> Bloquear município
                    </button>
                </form>
            @else
                <form method="post" action="{{ url('/apps-api/governanca/liberar') }}">
                    @csrf
                    <button class="botao" type="submit"><i class="fa fa-unlock"></i> Liberar município</button>
                </form>
            @endif
        </div>
    </div>
    @endif

    <div class="bloco passo" id="suspender">
        <h2><span class="passo-tag">B</span> Desativar acesso de pessoas</h2>
        <p class="sub">
            Elas saem dos aplicativos e do i-Educar na hora e só voltam após reativação.
            A lista respeita o seu nível (rede, escola ou mesma unidade).
        </p>
        <form method="get" action="{{ url()->current() }}" style="display:flex;gap:8px;flex-wrap:wrap;margin:0 0 12px">
            <input type="search" name="q" value="{{ $buscaUsuarios ?? '' }}" placeholder="Buscar por nome, matrícula ou número" style="min-width:220px;flex:1;min-height:36px;padding:6px 8px;border:1px solid var(--linha);border-radius:8px;font:inherit">
            <button class="botao claro" type="submit"><i class="fa fa-search"></i> Filtrar lista</button>
        </form>
        <form class="form-grid" method="post" action="{{ $postSuspender ?? url('/apps-api/governanca/suspender') }}" onsubmit="return confirm('Desativar os usuários selecionados? Eles serão desconectados de todos os aplicativos e do i-Educar.');">
            @csrf
            @if(!empty($redirectAposSuspender ?? null))
                <input type="hidden" name="_redirect" value="{{ $redirectAposSuspender }}">
            @endif
            @include('buriti-ui::select-multi', [
                'campo' => 'usuario_ids[]',
                'rotulo' => 'Usuários a desativar',
                'icone' => 'fa-users',
                'opcoes' => collect($usuariosAtivos ?? [])->map(fn ($u) => [
                    'id' => $u['id'],
                    'nome' => ($u['nome'] ?: '—').' · '.($u['login'] ?? ''),
                    'meta' => trim(($u['nivel_nome'] ?? '').' '.implode(', ', array_slice($u['escolas'] ?? [], 0, 2))),
                ])->all(),
                'selecionados' => [],
                'placeholder' => 'Digite o nome ou a matrícula…',
                'ajuda' => 'Marque uma ou mais pessoas. Digite para filtrar a lista.',
                'tamanho' => 10,
                'obrigatorio' => true,
            ])
            <label class="col-wide">Motivo
                <input type="text" name="motivo" maxlength="255" placeholder="Suspenso pela SME">
            </label>
            <div class="acoes" style="grid-column:1/-1;display:flex;justify-content:flex-end">
                <button class="botao perigo" type="submit" @if(empty($usuariosAtivos)) disabled @endif>
                    <i class="fa fa-ban"></i> Desativar selecionados
                </button>
            </div>
        </form>
    </div>

    <div class="bloco passo">
        <h2><span class="passo-tag">C</span> Tokens ativos nos apps</h2>
        <p class="sub">Dispositivos com sessão Sanctum (<code>apps-</code>). Revogar força novo login.</p>
        @if(empty($tokens))
            <p style="margin:0;color:var(--suave)">Nenhum token Sanctum com prefixo <code>apps-</code>.</p>
        @else
            <table>
                <thead>
                <tr>
                    <th>#</th>
                    <th>Dispositivo</th>
                    <th>Usuário</th>
                    <th>Criado</th>
                    <th>Último uso</th>
                    <th>Expira</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($tokens as $t)
                    <tr class="{{ $t['expirado'] ? 'off' : '' }}">
                        <td>{{ $t['id'] }}</td>
                        <td><code>{{ $t['name'] }}</code></td>
                        <td>{{ $t['login'] }} <small style="color:var(--suave)">({{ $t['usuario_id'] }})</small></td>
                        <td>{{ $t['created_at'] ?? '—' }}</td>
                        <td>{{ $t['last_used_at'] ?? '—' }}</td>
                        <td>
                            @if($t['expirado'])
                                <span class="badge fail">Expirado</span>
                            @else
                                {{ $t['expires_at'] ?? 'sem prazo' }}
                            @endif
                        </td>
                        <td>
                            <form method="post" action="{{ url('/apps-api/governanca/tokens/'.$t['id'].'/revogar') }}" onsubmit="return confirm('Revogar este token?')">
                                @csrf
                                <button class="botao claro" type="submit" style="padding:4px 8px;font-size:.78rem"><i class="fa fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="bloco passo">
        <h2><span class="passo-tag">D</span> Pessoas suspensas ou inativas</h2>
        <p class="sub">Reative quando a pessoa puder voltar a usar os apps e o i-Educar.</p>
        @if(empty($suspensos))
            <p style="margin:0;color:var(--suave)">Nenhum usuário inativo encontrado.</p>
        @else
            <table>
                <thead>
                <tr><th>ID</th><th>Login</th><th>Nome</th><th>Motivo</th><th>Suspenso por</th><th>Quando</th><th></th></tr>
                </thead>
                <tbody>
                @foreach($suspensos as $u)
                    <tr>
                        <td>{{ $u['id'] }}</td>
                        <td>{{ $u['login'] }}</td>
                        <td>{{ $u['nome'] ?? '—' }}</td>
                        <td>{{ $u['motivo'] ?? '—' }}</td>
                        <td>{{ $u['suspenso_por'] ?? '—' }}</td>
                        <td>{{ $u['suspenso_em'] ?? $u['atualizado'] ?? '—' }}</td>
                        <td>
                            <form method="post" action="{{ url('/apps-api/governanca/usuarios/'.$u['id'].'/reativar') }}">
                                @csrf
                                @if(!empty($redirectAposSuspender ?? null))
                                    <input type="hidden" name="_redirect" value="{{ $redirectAposSuspender }}">
                                @endif
                                <button class="botao" type="submit" style="padding:4px 8px;font-size:.78rem"><i class="fa fa-unlock"></i> Reativar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="bloco passo">
        <h2><span class="passo-tag">E</span> Histórico de ações</h2>
        <p class="sub">Bloqueios, suspensões, liberações e mudanças de configuração.</p>
        @if(empty($historico))
            <p style="margin:0;color:var(--suave)">Ainda não há eventos registrados.</p>
        @else
            <table>
                <thead>
                <tr><th>Quando</th><th>Ação</th><th>Ator</th><th>Alvo</th><th>Detalhe</th></tr>
                </thead>
                <tbody>
                @foreach($historico as $h)
                    <tr>
                        <td>{{ $h['created_at'] ?? '—' }}</td>
                        <td><span class="badge {{ str_contains($h['acao'], 'bloquear') || str_contains($h['acao'], 'suspender') || str_contains($h['acao'], 'revogar') ? 'fail' : 'ok' }}">{{ $h['acao'] }}</span></td>
                        <td>{{ $h['ator_login'] ?? '—' }}</td>
                        <td>{{ $h['alvo_login'] ?? '—' }}</td>
                        <td>{{ $h['detalhe'] ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
