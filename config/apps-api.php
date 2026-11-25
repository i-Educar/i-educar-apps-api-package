<?php

return [

    /*
    | Master switch do contrato apps (Android/iOS).
    | false = todas as rotas /api/apps/v1 retornam 503 (desligamento do município / manutenção).
    | Também respeita legacy.config.active_on_ieducar (Suspender cliente no i-Educar).
    */
    'enabled' => (bool) env('APPS_API_ENABLED', true),

    /*
    | Exigir Bearer em todas as rotas de domínio (catálogo incluso).
    | Públicos fixos: auth/login, auth/status, openapi.yaml
    */
    'require_login' => (bool) env('APPS_API_REQUIRE_LOGIN', true),

    /*
    | TTL do token Sanctum em minutos (null = sem expires_at explícito no createToken).
    */
    'token_ttl_minutes' => (int) env('APPS_API_TOKEN_TTL', 60 * 24 * 30),

    /*
    | Mensagem quando o município está suspenso / apps desligados.
    */
    'suspension_message' => env(
        'APPS_API_SUSPENSION_MESSAGE',
        'Acesso aos aplicativos indisponível para este município. Contate a SME.'
    ),

];
