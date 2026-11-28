# Roadmap — App Android unificado (Laguna / PE-77/2026)

**Backend:** `buriti/i-educar-apps-api-package` (`api/apps/v1`)  
**Documento completo para a equipe mobile:** [`DOC-TECNICO-APP-ANDROID-UNIFICADO.md`](./DOC-TECNICO-APP-ANDROID-UNIFICADO.md)  
**UI nativa / lojas:** equipe externa (`5.13-LOJA` / `5.14-LOJA`)  
**Gate Buriti:** API + este DOC — **não** inclui criar o APK (`GATE-100-POC.md`)

## Princípios

1. **Um APK** (equipe externa) com perfis Sanctum: `familia` · `professor` · `equipe` · `admin`.  
2. Offline-first no professor (`POST /professor/sync` idempotente).  
3. Equipe técnica: CameraX → QR almox → `POST /equipe/almox/confirmar`.  
4. Domínio nos pacotes Buriti / Portabilis / i-Diário — a API **orquestra**.  
5. OpenAPI / catálogo = fonte da verdade (`GET /catalogo`).

## Fases

### A0 — Fundação *(especificação Buriti · implementação mobile externa)*

Login, `auth/status`, flavors homolog/prod, tratamento 503/feature flags.

### A1 — Família (5.13) *(API Buriti · UI externa)*

Filhos, frequência, notas, fila, transporte, uniforme, cardápio, atendimento, biblioteca, busca ativa.

### A2 — Professor (5.14) *(API Buriti · UI externa)*

Turmas, chamada/notas, sync offline, histórico, horário, eventos, health i-Diário.

### A3 — Equipe técnica (Onda 10) *(API Buriti · UI externa)*

Painel, pedidos/notas almox, QR remessa, etiquetas, saldos, empréstimos, processos ACT.  
Assinatura digital: abrir LibreSign na web (deep link).

### A4 — Lojas *(somente equipe externa)*

Play Console, privacidade, deep links, sandbox handoff.

## Aceite mínimo

Ver checklist §7 do [DOC técnico](./DOC-TECNICO-APP-ANDROID-UNIFICADO.md).

## Referências

- Handoff: `docs/onda-3/HANDOFF-5.13-5.14.md`  
- Status Onda 10: `packages/buriti/docs/onda-10/STATUS.md`  
- OpenAPI: `docs/openapi-apps-v1.yaml`
