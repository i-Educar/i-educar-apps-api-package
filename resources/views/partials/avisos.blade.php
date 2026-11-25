@php $titulo = $titulo ?? 'Avisos de impacto'; @endphp
<div class="bloco" style="border-top:3px solid {{ empty($avisos) ? 'var(--ok)' : 'var(--ambar)' }}">
    <h2>
        <i class="fa {{ empty($avisos) ? 'fa-check-circle' : 'fa-exclamation-triangle' }}" style="color: {{ empty($avisos) ? 'var(--ok)' : 'var(--ambar)' }}"></i>
        {{ $titulo }}
    </h2>
    @if(empty($avisos))
        <p style="margin:0;color:var(--ok)">Tudo certo — as funções habilitadas estão disponíveis para os aplicativos.</p>
    @else
        <p class="sub">O que deixa de funcionar com a configuração atual (ou com a prévia).</p>
        <div class="aviso-lista">
            @foreach($avisos as $a)
                <article class="aviso {{ $a['nivel'] ?? 'warn' }}">
                    <header>
                        <span class="badge {{ ($a['nivel'] ?? 'warn') === 'fail' ? 'fail' : 'warn' }}">
                            {{ ($a['nivel'] ?? '') === 'fail' ? 'Crítico' : 'Atenção' }}
                        </span>
                        <strong>{{ $a['titulo'] }}</strong>
                    </header>
                    <p>{{ $a['corpo'] }}</p>
                    @if(!empty($a['funcoes']))
                        <ul>
                            @foreach(array_slice($a['funcoes'], 0, 8) as $fn)
                                <li><code>{{ $fn }}</code></li>
                            @endforeach
                            @if(count($a['funcoes']) > 8)
                                <li>… e mais {{ count($a['funcoes']) - 8 }} função(ões)</li>
                            @endif
                        </ul>
                    @endif
                </article>
            @endforeach
        </div>
    @endif
</div>
