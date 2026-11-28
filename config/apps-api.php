<?php

return [

    /*
    | Master switch do contrato apps (Android/iOS).
    | false = rotas /api/apps/v1 (exceto status/openapi) retornam 503.
    | Também respeita legacy.config.active_on_ieducar (Suspender cliente).
    */
    'enabled' => (bool) env('APPS_API_ENABLED', true),

    /*
    | Exigir Bearer em rotas de domínio (catálogo incluso).
    | Públicos fixos: auth/login, auth/status, openapi.yaml
    */
    'require_login' => (bool) env('APPS_API_REQUIRE_LOGIN', true),

    /*
    | TTL do token Sanctum em minutos (null = sem expires_at explícito).
    */
    'token_ttl_minutes' => (int) env('APPS_API_TOKEN_TTL', 60 * 24 * 30),

    /*
    | Mensagem quando o município está suspenso / apps desligados.
    */
    'suspension_message' => env(
        'APPS_API_SUSPENSION_MESSAGE',
        'Acesso aos aplicativos indisponível para este município. Contate a SME.'
    ),

    /*
    | Grupos e funções ligáveis na tela de configuração SME.
    | false = endpoint responde 503 com código apps_feature_disabled + aviso de impacto.
    */
    'features' => [
        'familia' => (bool) env('APPS_API_FEATURE_FAMILIA', true),
        'professor' => (bool) env('APPS_API_FEATURE_PROFESSOR', true),
        'equipe' => (bool) env('APPS_API_FEATURE_EQUIPE', true),
        'admin' => (bool) env('APPS_API_FEATURE_ADMIN', true),
        'exportacao' => (bool) env('APPS_API_FEATURE_EXPORTACAO', true),
        'diario' => (bool) env('APPS_API_FEATURE_DIARIO', true),
        'familia.filhos' => (bool) env('APPS_API_FEATURE_FAMILIA_FILHOS', true),
        'familia.frequencia' => (bool) env('APPS_API_FEATURE_FAMILIA_FREQUENCIA', true),
        'familia.notas' => (bool) env('APPS_API_FEATURE_FAMILIA_NOTAS', true),
        'familia.transporte' => (bool) env('APPS_API_FEATURE_FAMILIA_TRANSPORTE', true),
        'familia.cardapio' => (bool) env('APPS_API_FEATURE_FAMILIA_CARDAPIO', true),
        'familia.fila' => (bool) env('APPS_API_FEATURE_FAMILIA_FILA', true),
        'familia.atendimentos' => (bool) env('APPS_API_FEATURE_FAMILIA_ATENDIMENTOS', true),
        'familia.biblioteca' => (bool) env('APPS_API_FEATURE_FAMILIA_BIBLIOTECA', true),
        'familia.uniforme' => (bool) env('APPS_API_FEATURE_FAMILIA_UNIFORME', true),
        'familia.busca_ativa' => (bool) env('APPS_API_FEATURE_FAMILIA_BUSCA_ATIVA', true),
        'professor.turmas' => (bool) env('APPS_API_FEATURE_PROFESSOR_TURMAS', true),
        'professor.lancamentos' => (bool) env('APPS_API_FEATURE_PROFESSOR_LANCAMENTOS', true),
        'professor.horario' => (bool) env('APPS_API_FEATURE_PROFESSOR_HORARIO', true),
        'professor.eventos' => (bool) env('APPS_API_FEATURE_PROFESSOR_EVENTOS', true),
        'professor.historico' => (bool) env('APPS_API_FEATURE_PROFESSOR_HISTORICO', true),
        'equipe.almox' => (bool) env('APPS_API_FEATURE_EQUIPE_ALMOX', true),
        'equipe.biblioteca' => (bool) env('APPS_API_FEATURE_EQUIPE_BIBLIOTECA', true),
        'equipe.contratacoes' => (bool) env('APPS_API_FEATURE_EQUIPE_CONTRATACOES', true),
    ],

    /*
    | Portal público na home/login (sem URL pública ainda).
    */
    'public' => [
        'aparecer_na_home' => (bool) env('APPS_API_APARECER_NA_HOME', false),
        'titulo' => env('APPS_API_HOME_TITULO', 'Apps API'),
        'url' => env('APPS_API_HOME_URL'),
        'ordem' => (int) env('APPS_API_HOME_ORDEM', 40),
    ],

];
