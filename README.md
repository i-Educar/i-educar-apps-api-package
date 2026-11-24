# Apps API — pais/professores (TR 5.13 + 5.14)

Pacote Buriti para o i-Educar **2.12**. Backend único (**Sanctum**) para os apps Android/iOS.

Repo: https://github.com/i-Educar/i-educar-apps-api-package

## Onda 3 (atual)

```bash
php packages/buriti/i-educar-apps-api-package/tests/run.php
# públicos: GET /api/apps/v1/catalogo | /openapi.yaml | /diario/status
# auth:    POST /api/apps/v1/auth/login  {login,password,perfil:familia|professor}
# família: GET /api/apps/v1/familia/filhos (+ frequencia/notas/cardapio/…)
# prof.:   GET /api/apps/v1/professor/turmas | POST chamada|notas|sync
```

- OpenAPI: [`docs/openapi-apps-v1.yaml`](./docs/openapi-apps-v1.yaml)
- Handoff lojas: [`docs/onda-3/HANDOFF-5.13-5.14.md`](./docs/onda-3/HANDOFF-5.13-5.14.md)
- Status: [`docs/onda-3/STATUS.md`](./docs/onda-3/STATUS.md)
- Roadmap Android: [`docs/ROADMAP-ANDROID.md`](./docs/ROADMAP-ANDROID.md)

## Prefixo

`/api/apps/v1/...` — catálogo alinhado às rotas (sem endpoints fantasmas).
