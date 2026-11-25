# Apps API — pais/professores (TR 5.13 + 5.14)

Pacote Buriti para o i-Educar **2.12**. Backend único (**Sanctum**) para os apps Android/iOS.

Repo: https://github.com/i-Educar/i-educar-apps-api-package

## Compatibilidade

| Produto | Versão |
| --- | --- |
| **i-Educar** | **2.12.x** (min. 2.12.0) |
| **i-Diário** | Portabilis i-Diário (espelho Laguna) via API/SSO do core |
| Pacote | `0.6.0` |

Declarado em `composer.json` → `extra.ieducar` e `extra.idiario`.

## Onda 5 (atual — governança)

```bash
php packages/buriti/i-educar-apps-api-package/tests/run.php
# público: GET  /api/apps/v1/auth/status
# login:   POST /api/apps/v1/auth/login {login,password,perfil,device_name}
# dados:   Bearer obrigatório (família/professor)
# admin:   suspender usuário / bloquear município
php artisan apps-api:suspender-usuario MATRICULA
php artisan apps-api:bloquear-municipio --mensagem="..."
```

- Onda 5: [`docs/onda-5/STATUS.md`](./docs/onda-5/STATUS.md)
- OpenAPI: [`docs/openapi-apps-v1.yaml`](./docs/openapi-apps-v1.yaml)
- Handoff: [`docs/onda-3/HANDOFF-5.13-5.14.md`](./docs/onda-3/HANDOFF-5.13-5.14.md)
- Roadmap Android: [`docs/ROADMAP-ANDROID.md`](./docs/ROADMAP-ANDROID.md)

## Prefixo

`/api/apps/v1/...` — catálogo alinhado às rotas (sem endpoints fantasmas).
