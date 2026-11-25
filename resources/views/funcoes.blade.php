@extends('apps-api::layout')

@section('titulo', 'Funções — Apps da rede')
@section('migalha', 'Funções')
@section('icone-tela', 'fa-list-alt')
@section('titulo-tela', 'Revisar funções disponíveis')
@section('lead')
    Contrato real das rotas <code>/api/apps/v1</code>, com status conforme a configuração atual da SME.
@endsection

@section('acoes-topo')
    <a class="botao claro" href="{{ url('/apps-api/configuracao') }}"><i class="fa fa-sliders"></i> Voltar à configuração</a>
    <a class="botao" href="{{ url('/apps-api/governanca') }}"><i class="fa fa-shield"></i> Governança</a>
@endsection

@section('conteudo')
@php
    $total = 0; $disp = 0;
    foreach ($porGrupo as $itens) {
        foreach ($itens as $f) {
            $total++;
            if (!empty($f['disponivel'])) $disp++;
        }
    }
    $grupos = array_keys($porGrupo);
@endphp

<div class="numeros">
    <article>
        <span class="kpi-ico"><i class="fa fa-plug"></i></span>
        <span>Rotas no catálogo</span>
        <strong>{{ $total }}</strong>
    </article>
    <article style="--tom: var(--ok)">
        <span class="kpi-ico"><i class="fa fa-check"></i></span>
        <span>Disponíveis agora</span>
        <strong>{{ $disp }}</strong>
    </article>
    <article style="--tom: {{ ($total - $disp) > 0 ? 'var(--ambar)' : 'var(--cor)' }}">
        <span class="kpi-ico"><i class="fa fa-ban"></i></span>
        <span>Indisponíveis</span>
        <strong>{{ $total - $disp }}</strong>
    </article>
    <article>
        <span class="kpi-ico"><i class="fa fa-folder-open"></i></span>
        <span>Grupos</span>
        <strong>{{ count($grupos) }}</strong>
    </article>
</div>

@include('apps-api::partials.avisos', ['avisos' => $avisos, 'titulo' => 'Funções impactadas agora'])

@php $diag = $diagnostico ?? null; @endphp
@if($diag)
<div class="bloco">
    <h2><i class="fa fa-balance-scale"></i> Volume de endpoints</h2>
    <p class="sub">{{ $diag['total'] }} rotas no contrato — veredito <strong>{{ $diag['veredito'] }}</strong>. Não é necessário criar nem remover rotas TR agora.</p>
    <ul style="margin:0;padding-left:18px;color:var(--suave);font-size:.86rem">
        @foreach($diag['recomendacoes'] as $rec)
            <li>{{ $rec }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="bloco">
    <h2><i class="fa fa-filter"></i> Filtrar por grupo</h2>
    <div class="chips" id="chips-grupos">
        <button type="button" class="ativo" data-grupo="todos">Todos</button>
        @foreach($grupos as $g)
            <button type="button" data-grupo="{{ $g }}">{{ ucfirst($g) }}</button>
        @endforeach
        <button type="button" data-grupo="indisponiveis">Só indisponíveis</button>
    </div>
</div>

@foreach($porGrupo as $grupo => $itens)
    <div class="bloco grupo-funcoes" data-grupo-bloco="{{ $grupo }}">
        <h2><i class="fa fa-folder-open"></i> {{ ucfirst($grupo) }}</h2>
        <p class="sub">{{ count($itens) }} rota(s) neste grupo</p>
        <div style="overflow:auto">
            <table>
                <thead>
                <tr>
                    <th>Método</th>
                    <th>Path</th>
                    <th>Auth</th>
                    <th>Escopo</th>
                    <th>PII</th>
                    <th>Função</th>
                    <th>Status</th>
                    <th>Descrição</th>
                </tr>
                </thead>
                <tbody>
                @foreach($itens as $f)
                    <tr class="{{ !empty($f['disponivel']) ? '' : 'off' }}" data-disponivel="{{ !empty($f['disponivel']) ? '1' : '0' }}">
                        <td><code>{{ $f['metodo'] }}</code></td>
                        <td><code>{{ $f['path'] }}</code></td>
                        <td>{{ $f['auth'] }}</td>
                        <td><code>{{ $f['escopo'] ?? '—' }}</code></td>
                        <td>
                            @if(!empty($f['pii']))
                                <span class="badge fail">Sim</span>
                            @else
                                <span class="badge ok">Não</span>
                            @endif
                        </td>
                        <td>
                            @if(!empty($f['feature']))
                                {{ $featuresMeta[$f['feature']]['rotulo'] ?? $f['feature'] }}
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if(!empty($f['disponivel']))
                                <span class="badge ok">Disponível</span>
                            @else
                                <span class="badge fail">Indisponível</span>
                            @endif
                        </td>
                        <td>{{ $f['descricao'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach

<div class="atalho-card">
    <div>
        <h3>Precisa mudar o que está ligado?</h3>
        <p>Volte à configuração para ligar/desligar funções e salvar.</p>
    </div>
    <a class="botao" href="{{ url('/apps-api/configuracao') }}"><i class="fa fa-sliders"></i> Ajustar configuração</a>
</div>

<script>
(function () {
    var chips = document.getElementById('chips-grupos');
    if (!chips) return;
    chips.addEventListener('click', function (e) {
        var btn = e.target.closest('button[data-grupo]');
        if (!btn) return;
        chips.querySelectorAll('button').forEach(function (b) { b.classList.toggle('ativo', b === btn); });
        var g = btn.getAttribute('data-grupo');
        document.querySelectorAll('[data-grupo-bloco]').forEach(function (bloco) {
            if (g === 'todos') {
                bloco.style.display = '';
                bloco.querySelectorAll('tbody tr').forEach(function (tr) { tr.style.display = ''; });
                return;
            }
            if (g === 'indisponiveis') {
                var tem = false;
                bloco.querySelectorAll('tbody tr').forEach(function (tr) {
                    var off = tr.getAttribute('data-disponivel') !== '1';
                    tr.style.display = off ? '' : 'none';
                    if (off) tem = true;
                });
                bloco.style.display = tem ? '' : 'none';
                return;
            }
            var match = bloco.getAttribute('data-grupo-bloco') === g;
            bloco.style.display = match ? '' : 'none';
            if (match) {
                bloco.querySelectorAll('tbody tr').forEach(function (tr) { tr.style.display = ''; });
            }
        });
    });
})();
</script>
@endsection
