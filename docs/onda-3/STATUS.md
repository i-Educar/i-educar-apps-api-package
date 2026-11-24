# Status — Onda 3 (Apps API)

Gate: OpenAPI/Postman ≥ 85% dos itens de dados 5.13/5.14 + handoff formal.

## Entregue

- [x] 3.1 Auth Sanctum (`POST auth/login`, `logout`, `me`) com ability `familia`|`professor`
- [x] 3.2 Família: filhos / frequência / notas (Eloquent no núcleo)
- [x] 3.3 Família: fila / rematrícula (contrato pré-matrícula)
- [x] 3.4 Família: cardápio (pacote 5.2 ou fallback)
- [x] 3.5 Família: transporte (contrato 5.4)
- [x] 3.6 Professor: turmas (Eloquent), chamada/notas/sync idempotente
- [x] 3.7 Professor: histórico funcional / lotação (5.1)
- [x] 3.8 OpenAPI + handoff (`docs/openapi-apps-v1.yaml`, `HANDOFF-5.13-5.14.md`)
- [x] Catálogo = rotas reais (sem endpoints fantasmas)
- [x] Pacote wired no monorepo Laguna (`composer` + provider)
- [x] Testes `tests/run.php` (cobertura TR ≥ 85%)

## Limitações honestas (PoC)

- Chamada/notas do professor entram em **fila idempotente** (não gravam direto em `modules.*` nesta onda; destino i-Diário/núcleo na Onda 4).
- Cardápio/atendimento/biblioteca/horário/eventos expõem **contrato estável**; persistência completa segue roadmaps dos pacotes de domínio.
- UI lojas Android/iOS = equipe externa (`5.13-LOJA` / `5.14-LOJA`).

## Verificação rápida

```bash
php packages/buriti/i-educar-apps-api-package/tests/run.php
curl -s localhost/api/apps/v1/catalogo | jq .cobertura_tr
curl -s -X POST localhost/api/apps/v1/auth/login \
  -H 'Content-Type: application/json' \
  -d '{"login":"admin","password":"...","perfil":"professor"}'
```
