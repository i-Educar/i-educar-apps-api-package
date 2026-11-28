@extends('apps-api::layout')

@section('titulo', 'Configurar — Apps da rede')
@section('migalha', 'Configurar')
@section('icone-tela', 'fa-sliders')
@section('titulo-tela', 'Configurar os aplicativos')
@section('lead')
    Defina se a API está ligada, o tempo do token e quais funções cada app pode usar. Depois revise o impacto e salve.
@endsection

@section('acoes-topo')
    @if($govOk)
        <span class="badge ok"><i class="fa fa-check"></i> Município liberado</span>
    @else
        <span class="badge fail"><i class="fa fa-ban"></i> Apps suspensos</span>
    @endif
    <a class="botao claro icone" href="{{ url('/apps-api/governanca') }}" title="Governança" aria-label="Governança"><i aria-hidden="true" class="fa fa-shield"></i></a>
@endsection

@section('conteudo')
@php
    $gruposFeature = [];
    foreach ($featuresMeta as $chave => $meta) {
        $g = $meta['grupo'] ?? 'outros';
        $gruposFeature[$g][$chave] = $meta;
    }
    $rotuloGrupo = [
        'perfil' => 'Apps principais',
        'familia' => 'Funções do app família',
        'professor' => 'Funções do app professor',
        'meta' => 'Serviços auxiliares',
        'outros' => 'Outras funções',
    ];
@endphp

<div class="hero-status {{ $govOk && !empty($estado['enabled']) ? '' : 'suspenso' }}">
    <div class="lado">
        <span class="bolha"><i class="fa {{ !empty($estado['enabled']) && $govOk ? 'fa-mobile' : 'fa-pause' }}"></i></span>
        <div>
            <h2>
                @if(!$govOk)
                    Apps suspensos no município
                @elseif(!empty($estado['enabled']))
                    Apps API em operação
                @else
                    Apps API desligada
                @endif
            </h2>
            <p>
                @if(!$govOk)
                    Ninguém entra pelos aplicativos até liberar em Governança.
                @elseif(!empty($estado['enabled']))
                    Família e professor autenticam com Sanctum · TTL {{ $estado['token_ttl_minutes'] }} min · cobertura TR {{ $cobertura['percentual'] ?? 0 }}%.
                @else
                    Login e rotas de domínio respondem 503. OpenAPI e status continuam públicos.
                @endif
            </p>
        </div>
    </div>
    <div class="acoes-tela">
        <a class="botao claro" href="{{ url('/apps-api/funcoes') }}"><i class="fa fa-list-alt"></i> Ver funções</a>
        @if(!$govOk)
            <a class="botao" href="{{ url('/apps-api/governanca') }}"><i class="fa fa-unlock"></i> Liberar município</a>
        @endif
    </div>
</div>

<div class="numeros">
    <article style="--tom: {{ !empty($estado['enabled']) ? 'var(--ok)' : 'var(--erro)' }}">
        <span class="kpi-ico"><i class="fa fa-power-off"></i></span>
        <span>API master</span>
        <strong>{{ !empty($estado['enabled']) ? 'Ligada' : 'Desligada' }}</strong>
    </article>
    <article style="--tom: {{ !empty($estado['apps_habilitados']) ? 'var(--ok)' : 'var(--ambar)' }}">
        <span class="kpi-ico"><i class="fa fa-check-circle"></i></span>
        <span>Apps no município</span>
        <strong>{{ !empty($estado['apps_habilitados']) ? 'Sim' : 'Não' }}</strong>
    </article>
    <article>
        <span class="kpi-ico"><i class="fa fa-lock"></i></span>
        <span>Login obrigatório</span>
        <strong>{{ !empty($estado['require_login']) ? 'Sim' : 'Não' }}</strong>
    </article>
    <article>
        <span class="kpi-ico"><i class="fa fa-clock-o"></i></span>
        <span>TTL do token</span>
        <strong>{{ $estado['token_ttl_minutes'] }} min</strong>
    </article>
    <article>
        <span class="kpi-ico"><i class="fa fa-key"></i></span>
        <span>Tokens ativos</span>
        <strong>{{ $estado['tokens_ativos'] }}</strong>
    </article>
    <article style="--tom: {{ ($monitorResumo['suspensos'] ?? 0) > 0 ? 'var(--ambar)' : 'var(--cor)' }}">
        <span class="kpi-ico"><i class="fa fa-user-times"></i></span>
        <span>Suspensos</span>
        <strong>{{ $monitorResumo['suspensos'] ?? 0 }}</strong>
    </article>
</div>

@include('apps-api::partials.avisos', ['avisos' => $avisos, 'titulo' => 'Impacto atual'])

@php $diag = $diagnostico ?? ['total' => 0, 'por_grupo' => [], 'veredito' => 'adequado', 'recomendacoes' => []]; @endphp
<div class="bloco" id="painel-seguranca">
    <h2><i class="fa fa-shield"></i> Segurança, privacidade e volume de endpoints</h2>
    <p class="sub">
        Catálogo com <strong>{{ $diag['total'] }}</strong> rotas reais — veredito:
        <span class="badge ok">{{ $diag['veredito'] ?? 'adequado' }}</span>
        (não falta nem sobra contrato TR; Busca Ativa/Notificador ficam em módulos próprios).
    </p>
    <div class="chips" style="margin:0 0 12px">
        @foreach(($diag['por_grupo'] ?? []) as $g => $n)
            <span class="badge">{{ ucfirst($g) }}: {{ $n }}</span>
        @endforeach
        <span class="badge ok">Login throttle 10/min</span>
        <span class="badge ok">Perfil com vínculo</span>
        <span class="badge ok">Escrita professor = turma própria</span>
    </div>
    <ul style="margin:0;padding-left:18px;color:var(--suave);font-size:.86rem;line-height:1.45">
        @foreach(($diag['recomendacoes'] ?? []) as $rec)
            <li>{{ $rec }}</li>
        @endforeach
    </ul>
</div>

<form method="post" action="{{ url('/apps-api/configuracao') }}" id="form-apps-config">
    @csrf

    <div class="bloco passo" id="passo-auth">
        <h2><span class="passo-tag">1</span> Autenticação</h2>
        <p class="sub">Controle geral da API e do tempo de sessão nos celulares. TTL curto reduz risco se o aparelho for perdido.</p>
        <div class="form-grid">
            <label class="switch col-wide">
                <input type="checkbox" name="enabled" value="1" @checked($estado['enabled'])>
                <span class="txt">
                    <strong>Ligar a Apps API neste município</strong>
                    <small>Desligar envia HTTP 503 no login e nas rotas de domínio. Status e OpenAPI seguem públicos.</small>
                </span>
            </label>
            <label class="switch col-wide">
                <input type="checkbox" name="require_login" value="1" @checked($estado['require_login'])>
                <span class="txt">
                    <strong>Exigir token (Bearer / Sanctum) nas rotas</strong>
                    <small>Recomendado em produção. Públicos fixos: login, status e openapi.yaml.</small>
                </span>
            </label>
            <label>
                Tempo do token (minutos)
                <input type="number" name="token_ttl_minutes" min="5" max="525600" value="{{ $estado['token_ttl_minutes'] }}" required>
                <small style="display:block;margin-top:4px;color:var(--suave)">Sugestão produção: 10080 (7 dias) a 43200 (30 dias).</small>
            </label>
            <label class="col-wide">
                Mensagem quando os apps estiverem suspensos
                <textarea name="suspension_message" rows="2" placeholder="Texto curto que a pessoa vê no celular">{{ $estado['suspension_message'] }}</textarea>
            </label>
        </div>
    </div>

    <div class="bloco passo" id="passo-funcoes">
        <h2><span class="passo-tag">2</span> Funções dos aplicativos</h2>
        <p class="sub">Marque o que cada app pode usar. Funções com PII (filhos, frequência, notas, lançamentos) respeitam vínculo e podem ser desligadas sem derrubar o app inteiro.</p>

        @foreach($gruposFeature as $grupo => $itens)
            <section class="feature-grupo">
                <header>
                    <h3>{{ $rotuloGrupo[$grupo] ?? ucfirst($grupo) }}</h3>
                    <span>{{ count($itens) }} opção(ões)</span>
                </header>
                <div class="feature-grid">
                    @foreach($itens as $chave => $meta)
                        @php
                            $ativa = (bool) ($estado['features'][$chave] ?? true);
                            $paiOff = !empty($meta['depende']) && empty($estado['features'][$meta['depende']]);
                        @endphp
                        <label class="switch" style="{{ $paiOff ? 'opacity:.55' : '' }}">
                            <input type="checkbox"
                                   name="features[{{ $chave }}]"
                                   value="1"
                                   data-feature="{{ $chave }}"
                                   data-depende="{{ $meta['depende'] ?? '' }}"
                                   @checked($ativa)
                                   @disabled($paiOff)>
                            <span class="txt">
                                <strong>{{ $meta['rotulo'] }}</strong>
                                <small>{{ $meta['descricao'] }}</small>
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>

    <div class="bloco passo" id="passo-revisar">
        <h2><span class="passo-tag">3</span> Revisar e salvar</h2>
        <p class="sub">Ao salvar, o painel de impacto mostra o que fica indisponível. Você pode conferir o catálogo completo a qualquer momento.</p>
        <div class="atalho-card">
            <div>
                <h3><i class="fa fa-list-alt" style="color:var(--cor)"></i> Catálogo de funções</h3>
                <p>Lista método, path e status de cada rota <code>/api/apps/v1</code>.</p>
            </div>
            <a class="botao claro" href="{{ url('/apps-api/funcoes') }}"><i class="fa fa-arrow-right"></i> Abrir revisão</a>
        </div>
    </div>

    <div class="barra-salvar">
        <p><i class="fa fa-info-circle"></i> As alterações valem na hora para os aplicativos da rede.</p>
        <div class="acoes-tela">
            <a class="botao claro" href="{{ url('/apps-api/funcoes') }}"><i class="fa fa-eye"></i> Só revisar</a>
            <button class="botao" type="submit"><i class="fa fa-save"></i> Salvar configuração</button>
        </div>
    </div>
</form>

<script>
(function () {
    var form = document.getElementById('form-apps-config');
    if (!form) return;
    form.querySelectorAll('input[data-feature]').forEach(function (cb) {
        cb.addEventListener('change', function () {
            var chave = cb.getAttribute('data-feature');
            form.querySelectorAll('input[data-depende="' + chave + '"]').forEach(function (filho) {
                if (!cb.checked) {
                    filho.checked = false;
                    filho.disabled = true;
                } else {
                    filho.disabled = false;
                }
            });
        });
    });
})();
</script>
@endsection
