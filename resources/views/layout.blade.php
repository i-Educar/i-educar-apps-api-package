<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Apps da rede')</title>
    <link rel="stylesheet" href="{{ url('/intranet/styles/font-awesome.css') }}">
    <style>
        :root {
            --cor: {{ $identidade['cor'] ?? '#0f5c6e' }};
            --destaque: {{ $identidade['destaque'] ?? '#1a7f90' }};
            --clara: {{ $identidade['clara'] ?? '#e7f4f6' }};
            --nav: #122029; --nav-texto: #d5e0e6; --fundo: #e9eef1;
            --tinta: #1a242b; --suave: #5b6b76; --linha: #cfd8df;
            --ok: #17663f; --ok-fundo: #e5f6ec; --erro: #9b2c2c; --erro-fundo: #fdeceb;
            --ambar: #b86e00; --cor-tela: var(--cor);
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: "Segoe UI", system-ui, sans-serif; font-size: 14px; color: var(--tinta);
            background:
                radial-gradient(820px 340px at 6% -10%, color-mix(in srgb, var(--clara) 85%, transparent), transparent 55%),
                linear-gradient(180deg, #f4f7f9, var(--fundo)); }
        .app { display: flex; min-height: 100vh; }
        .lateral { width: 248px; flex: none; background: linear-gradient(180deg, color-mix(in srgb, var(--cor) 28%, #122029), var(--nav)); color: var(--nav-texto); }
        .marca { display: flex; align-items: center; justify-content: center; color: #fff; text-decoration: none; padding: 14px; border-bottom: 1px solid rgba(255,255,255,.08); }
        .marca .marca-texto { display: none; }
        .marca .marca-titulo { display: block; font-size: .9rem; font-weight: 700; line-height: 1.2; }
        .marca .marca-desc { display: block; color: #8aa0ad; font-weight: 400; font-size: .72rem; line-height: 1.25; }
        .icone { width: 40px; height: 40px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center;
            background: linear-gradient(145deg, var(--destaque), var(--cor)); color: #fff; box-shadow: 0 8px 18px color-mix(in srgb, var(--cor) 30%, transparent); }
        .modulo { padding: 8px 10px; }
        .modulo-nome { margin: 0; padding: 8px; color: #8aa0ad; font-size: 11px; letter-spacing: .06em; text-transform: uppercase; font-weight: 700; }
        .modulo a { display: flex; align-items: center; gap: 10px; color: var(--nav-texto); text-decoration: none; border-radius: 10px; padding: 9px 10px; }
        .modulo a i { width: 18px; text-align: center; opacity: .9; }
        .modulo a small { display: block; font-size: .68rem; font-weight: 500; color: #8aa0ad; margin-top: 1px; }
        .modulo a:hover, .modulo a.ativo { background: color-mix(in srgb, var(--cor) 82%, #fff); color: #fff; font-weight: 700; }
        .modulo a.ativo small { color: rgba(255,255,255,.78); }
        .miolo { flex: 1; min-width: 0; display: flex; flex-direction: column; }
        main { padding: 18px 20px 36px; flex: 1; }
        .cabeca-tela { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin: -18px -20px 16px; padding: 14px 16px 14px 20px;
            background: linear-gradient(120deg, color-mix(in srgb, var(--clara) 72%, #fff), #fff 58%); border-bottom: 1px solid var(--linha); border-left: 4px solid var(--cor-tela); }
        .identidade-tela { display: flex; align-items: center; gap: 12px; }
        .marca-tela { width: 46px; height: 46px; border-radius: 14px; display: grid; place-items: center; background: linear-gradient(145deg, var(--destaque), var(--cor-tela)); color: #fff; font-size: 18px; box-shadow: 0 10px 20px color-mix(in srgb, var(--cor-tela) 24%, transparent); }
        .cabeca-tela h1 { margin: 0; font-size: 1.22rem; font-weight: 700; }
        .cabeca-tela .lead { margin: 3px 0 0; color: var(--suave); max-width: 52rem; }
        .acoes-tela { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .botao { display: inline-flex; align-items: center; gap: 8px; background: var(--cor-tela); color: #fff; border: 0; border-radius: 11px; padding: 9px 13px; font: inherit; text-decoration: none; cursor: pointer; transition: transform .12s ease, filter .12s ease; }
        .botao:hover { filter: brightness(1.05); transform: translateY(-1px); }
        .botao.claro { background: #fff; color: var(--cor-tela); border: 1px solid color-mix(in srgb, var(--cor-tela) 30%, var(--linha)); }
        .botao.perigo { background: var(--erro); }
        .botao:disabled { opacity: .5; cursor: not-allowed; transform: none; }
        .flash { border-radius: 12px; padding: 12px 14px; margin: 0 0 16px; }
        .flash.ok { background: var(--ok-fundo); color: var(--ok); }
        .flash.erro { background: var(--erro-fundo); color: var(--erro); }

        .fluxo-passos { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin: 0 0 16px; }
        .fluxo-passos a {
            display: grid; grid-template-columns: 34px 1fr; gap: 10px; align-items: center;
            padding: 12px 14px; border-radius: 14px; text-decoration: none; color: var(--tinta);
            background: #fff; border: 1px solid var(--linha); box-shadow: 0 8px 18px rgba(21,34,43,.03);
            transition: border-color .15s ease, transform .15s ease;
        }
        .fluxo-passos a:hover { border-color: color-mix(in srgb, var(--cor) 40%, var(--linha)); transform: translateY(-1px); }
        .fluxo-passos a.ativo { border-color: color-mix(in srgb, var(--cor) 45%, var(--linha)); background: linear-gradient(180deg, color-mix(in srgb, var(--clara) 70%, #fff), #fff); }
        .fluxo-passos .num {
            width: 34px; height: 34px; border-radius: 11px; display: grid; place-items: center;
            background: color-mix(in srgb, var(--clara) 80%, #fff); color: var(--cor); font-weight: 800; font-size: .85rem;
        }
        .fluxo-passos a.ativo .num { background: var(--cor); color: #fff; }
        .fluxo-passos strong { display: block; font-size: .9rem; }
        .fluxo-passos small { display: block; color: var(--suave); font-size: .74rem; margin-top: 2px; font-weight: 500; }

        .hero-status {
            display: flex; flex-wrap: wrap; gap: 14px; align-items: center; justify-content: space-between;
            padding: 16px 18px; border-radius: 16px; margin: 0 0 16px;
            background: linear-gradient(120deg, color-mix(in srgb, var(--clara) 65%, #fff), #fff);
            border: 1px solid color-mix(in srgb, var(--cor) 18%, var(--linha));
        }
        .hero-status.suspenso { border-color: color-mix(in srgb, var(--erro) 35%, #fff); background: linear-gradient(120deg, #fff5f4, #fff); }
        .hero-status .lado { display: flex; gap: 12px; align-items: center; min-width: 0; }
        .hero-status .bolha {
            width: 48px; height: 48px; border-radius: 14px; display: grid; place-items: center; color: #fff; font-size: 1.15rem;
            background: linear-gradient(145deg, var(--destaque), var(--cor));
        }
        .hero-status.suspenso .bolha { background: linear-gradient(145deg, #c45a5a, var(--erro)); }
        .hero-status h2 { margin: 0; font-size: 1.05rem; }
        .hero-status p { margin: 3px 0 0; color: var(--suave); font-size: .86rem; }

        .numeros { display: grid; grid-template-columns: repeat(auto-fill, minmax(148px, 1fr)); gap: 10px; margin: 0 0 16px; }
        .numeros article {
            --tom: var(--cor);
            background: #fff; border: 1px solid color-mix(in srgb, var(--tom) 22%, var(--linha));
            border-radius: 14px; padding: 12px 14px; box-shadow: 0 8px 16px rgba(21,34,43,.03);
        }
        .numeros .kpi-ico {
            width: 30px; height: 30px; border-radius: 9px; display: grid; place-items: center; margin: 0 0 8px;
            background: color-mix(in srgb, var(--tom) 12%, #fff); color: var(--tom);
        }
        .numeros span { display: block; color: var(--suave); font-size: .72rem; font-weight: 700; letter-spacing: .03em; text-transform: uppercase; }
        .numeros strong { font-size: 1.28rem; letter-spacing: -.02em; }

        .bloco { background: #fff; border: 1px solid var(--linha); border-radius: 16px; padding: 16px 18px; margin: 0 0 14px; box-shadow: 0 10px 24px rgba(21,34,43,.035); }
        .bloco.passo { border-top: 3px solid var(--cor-tela); }
        .bloco h2 { margin: 0 0 6px; display: flex; align-items: center; gap: 8px; font-size: 1.02rem; }
        .bloco .sub { margin: 0 0 14px; color: var(--suave); font-size: .86rem; }
        .passo-tag {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 24px; height: 24px; padding: 0 7px; border-radius: 999px;
            background: var(--clara); color: var(--cor); font-size: .72rem; font-weight: 800;
        }

        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 999px; font-size: .75rem; font-weight: 700; }
        .badge.ok { background: var(--ok-fundo); color: var(--ok); }
        .badge.fail { background: var(--erro-fundo); color: var(--erro); }
        .badge.warn { background: #fff6e8; color: var(--ambar); }

        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 12px; align-items: end; }
        .form-grid label { display: grid; gap: 4px; font-size: .8rem; font-weight: 700; color: var(--suave); }
        .form-grid input, .form-grid select, .form-grid textarea { min-height: 38px; padding: 7px 10px; border: 1px solid var(--linha); border-radius: 10px; font: inherit; background: #fff; }
        .form-grid textarea { min-height: 72px; resize: vertical; }
        .form-grid .col-wide { grid-column: 1 / -1; }
        .form-grid .acoes { grid-column: 1 / -1; display: flex; justify-content: flex-end; gap: 8px; }

        .switch {
            display: flex; align-items: flex-start; gap: 12px; padding: 12px 14px;
            border: 1px solid var(--linha); border-radius: 14px; background: #fbfcfd;
            transition: border-color .15s ease, background .15s ease;
        }
        .switch:hover { border-color: color-mix(in srgb, var(--cor) 30%, var(--linha)); background: #fff; }
        .switch input { width: 18px; height: 18px; margin-top: 2px; flex: none; accent-color: var(--cor); }
        .switch .txt strong { display: block; color: var(--tinta); font-size: .9rem; }
        .switch .txt small { color: var(--suave); font-weight: 500; line-height: 1.35; }

        .feature-grupo { margin: 0 0 16px; }
        .feature-grupo > header {
            display: flex; align-items: baseline; justify-content: space-between; gap: 10px;
            margin: 0 0 8px; padding: 0 2px;
        }
        .feature-grupo > header h3 { margin: 0; font-size: .82rem; letter-spacing: .04em; text-transform: uppercase; color: var(--cor); }
        .feature-grupo > header span { color: var(--suave); font-size: .75rem; }
        .feature-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(270px, 1fr)); gap: 10px; }

        .barra-salvar {
            position: sticky; bottom: 12px; z-index: 5;
            display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between;
            padding: 12px 14px; border-radius: 14px; margin-top: 8px;
            background: color-mix(in srgb, #fff 92%, transparent); backdrop-filter: blur(8px);
            border: 1px solid var(--linha); box-shadow: 0 12px 28px rgba(21,34,43,.08);
        }
        .barra-salvar p { margin: 0; color: var(--suave); font-size: .84rem; }

        .aviso-lista { display: grid; gap: 10px; }
        .aviso { border: 1px solid var(--linha); border-radius: 12px; padding: 12px; border-left: 4px solid var(--ambar); background: #fffdf8; }
        .aviso.fail { border-left-color: var(--erro); background: #fff8f7; }
        .aviso header { display: flex; gap: 8px; align-items: center; margin: 0 0 6px; }
        .aviso p { margin: 0 0 8px; color: var(--suave); }
        .aviso ul { margin: 0; padding-left: 18px; color: var(--tinta); font-size: .85rem; }
        .aviso code { font-size: .78rem; }

        .chips { display: flex; flex-wrap: wrap; gap: 6px; margin: 0 0 12px; }
        .chips a, .chips button {
            appearance: none; border: 1px solid var(--linha); background: #fff; color: var(--suave);
            border-radius: 999px; padding: 6px 12px; font: inherit; font-size: .78rem; font-weight: 700;
            text-decoration: none; cursor: pointer;
        }
        .chips a.ativo, .chips button.ativo { background: var(--clara); color: var(--cor); border-color: color-mix(in srgb, var(--cor) 28%, var(--linha)); }

        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 9px 10px; border-bottom: 1px solid var(--linha); font-size: .85rem; }
        th { background: color-mix(in srgb, var(--clara) 70%, #fff); color: var(--cor); font-weight: 700; }
        tr.off { opacity: .55; background: #fafafa; }
        code { font-size: .8rem; background: #f3f6f8; padding: 2px 6px; border-radius: 6px; }

        .atalho-card {
            display: flex; gap: 12px; align-items: center; justify-content: space-between; flex-wrap: wrap;
            padding: 14px 16px; border-radius: 14px; border: 1px dashed color-mix(in srgb, var(--cor) 28%, var(--linha));
            background: color-mix(in srgb, var(--clara) 45%, #fff); margin: 0 0 14px;
        }
        .atalho-card p { margin: 2px 0 0; color: var(--suave); font-size: .84rem; }
        .atalho-card h3 { margin: 0; font-size: .95rem; }

        @media (max-width: 900px) {
            .lateral { display: none; }
            .fluxo-passos { grid-template-columns: 1fr; }
        }
    </style>
    @include('buriti-ui::estilos')
</head>
<body>
@php
    $identidade = $identidade ?? \iEducar\Packages\BuritiAppsApi\Support\Identidade::tokens();
    $path = request()->path();
@endphp
<div class="app">
    <aside class="lateral">
        <a class="marca" href="{{ url('/apps-api/configuracao') }}" title="{{ $identidade['nome'] ?? '' }}">
            <span class="icone"><i class="fa {{ $identidade['icone'] ?? 'fa-mobile' }}"></i></span>
        </a>
        <nav class="modulo">
            <div class="modulo-nome">Fluxo</div>
            <a href="{{ url('/apps-api/configuracao') }}" class="@if(request()->is('apps-api/configuracao')) ativo @endif">
                <i class="fa fa-sliders"></i>
                <span>1. Configurar<small>API, tokens e funções</small></span>
            </a>
            <a href="{{ url('/apps-api/funcoes') }}" class="@if(request()->is('apps-api/funcoes')) ativo @endif">
                <i class="fa fa-list-alt"></i>
                <span>2. Revisar funções<small>O que cada app usa</small></span>
            </a>
            <a href="{{ url('/apps-api/governanca') }}" class="@if(request()->is('apps-api/governanca*')) ativo @endif">
                <i class="fa fa-shield"></i>
                <span>3. Governar acessos<small>Tokens e suspensões</small></span>
            </a>
        </nav>
        <nav class="modulo">
            <div class="modulo-nome">Referência</div>
            <a href="{{ url('/api/apps/v1/openapi.yaml') }}" target="_blank" rel="noopener"><i class="fa fa-file-code-o"></i> Contrato OpenAPI</a>
            <a href="{{ url('/laguna-ambiente') }}"><i class="fa fa-heartbeat"></i> Ambiente Laguna</a>
        </nav>
    </aside>
    <div class="miolo">
        @php
            $homeModulo = url('/apps-api/configuracao');
            $nomeModulo = $identidade['nome'] ?? 'Apps da rede';
            $descricaoModulo = $identidade['descricao'] ?? null;
            $menuGrupos = [
                [
                    'nome' => 'Fluxo',
                    'itens' => [
                        ['rotulo' => 'Configurar', 'url' => '/apps-api/configuracao', 'icone' => 'fa-sliders'],
                        ['rotulo' => 'Funções', 'url' => '/apps-api/funcoes', 'icone' => 'fa-list-alt'],
                        ['rotulo' => 'Governança', 'url' => '/apps-api/governanca', 'icone' => 'fa-shield'],
                    ],
                ],
            ];
        @endphp
        @include('buriti-ui::topo')
        <main>
            @if(session('ok')) <p class="flash ok">{{ session('ok') }}</p> @endif
            @if(session('erro')) <p class="flash erro">{{ session('erro') }}</p> @endif

            <header class="cabeca-tela">
                <div class="identidade-tela">
                    <span class="marca-tela"><i class="fa @yield('icone-tela', $identidade['icone'] ?? 'fa-mobile')" aria-hidden="true"></i></span>
                    <div>
                        <h1>@hasSection('titulo-tela')@yield('titulo-tela')@else@yield('migalha', 'Painel')@endif</h1>
                        @hasSection('lead')
                            <p class="lead">@yield('lead')</p>
                        @endif
                    </div>
                </div>
                <div class="acoes-tela">@yield('acoes-topo')</div>
            </header>

            <nav class="fluxo-passos" aria-label="Passos do módulo">
                <a href="{{ url('/apps-api/configuracao') }}" class="@if(request()->is('apps-api/configuracao')) ativo @endif">
                    <span class="num">1</span>
                    <span><strong>Configurar</strong><small>Ligar API e escolher funções</small></span>
                </a>
                <a href="{{ url('/apps-api/funcoes') }}" class="@if(request()->is('apps-api/funcoes')) ativo @endif">
                    <span class="num">2</span>
                    <span><strong>Revisar funções</strong><small>Ver o que fica disponível</small></span>
                </a>
                <a href="{{ url('/apps-api/governanca') }}" class="@if(request()->is('apps-api/governanca*')) ativo @endif">
                    <span class="num">3</span>
                    <span><strong>Governar</strong><small>Tokens, bloqueios e pessoas</small></span>
                </a>
            </nav>

            @yield('conteudo')
        </main>
        @include('buriti-ui::rodape', ['moduloRodape' => 'Apps da rede'])
    </div>
</div>
</body>
</html>
