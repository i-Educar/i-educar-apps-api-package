# EPIC — APIs para Apps (família e professor)

| Campo | Valor |
| --- | --- |
| Pacote | `buriti/i-educar-apps-api-package` |
| TR Laguna | **5.13** (pais/responsáveis) + **5.14** (professores) |
| Prioridade | **P0** |
| Nota atual (executivo) | **D/C** (lojas) · APIs = pré-requisito Buriti |
| Fonte | `packages/buriti/docs/laguna/DOC-EXECUTIVO-PE-77-2026-LAGUNA-IEDUCAR.md` |
| Regra de ouro | Pregão Eletrônico 77/2026 · Laguna/SC · PoC ≥ 85% por módulo · gaps ≤ 30 dias |
| Base atual | Portais web e i-Diário; **não** há app nas lojas neste repositório |

## Objetivo

Expor **um único pacote de APIs** (contrato estável, versionado e documentado) com tudo que os apps Android/iOS precisam obter e enviar — para que **outra equipe**, em ambiente próprio, construa a interface e o código nativo/PWA das lojas. Este pacote **não** inclui UI mobile, builds de loja nem publicação na Play Store/App Store.

## Padrão técnico (i-Educar 2.12)

| Item | Valor |
| --- | --- |
| Alvo | i-Educar **2.12** · PHP **≥ 8.3** · Illuminate **^11|^12|^13** (Laravel 13 no app) |
| Composer | `buriti/i-educar-apps-api-package` |
| Namespace | `iEducar\Packages\BuritiAppsApi\` → `src/` |
| Compat | `extra.ieducar.compatible: ["2.12"]` · GPL-2.0-or-later · `type: library` |
| Schema / dados | apps_api (apenas metadados: devices, push, refresh) — dados de domínio ficam nos pacotes fontes |
| Rotas | prefixo `api/apps/v1`; SME com middleware `ieducar.*` + `auth`; API apps via Sanctum quando exposto |
| Tenancy | `PackageTenant::allows('buriti/i-educar-apps-api-package')` antes de migrations/menu |
| Plug-and-play | path em `packages/buriti/…`; manter em `ignore` até instalável |
| Referência | `packages/buriti/PADRAO-PACOTE-2.12.md` |

**Nota de arquitetura:** Só API + OpenAPI. Auth = Laravel Sanctum (já no 2.12). Sem UI Android/iOS neste monorepo.

## Cobertura 100% do TR

Este EPIC cobre **todos** os requisitos do módulo(s) 5.13 e 5.14 listados em [`REQUISITOS-TR.md`](./REQUISITOS-TR.md) (48 itens OCR).

- PoC: ≥ **85%** demonstráveis e operacionais.
- Até **15%** restante: produção em ≤ **30 dias**, sem ônus.
- Nenhum item do `REQUISITOS-TR.md` pode ficar fora do `ROADMAP.md` / `TODO.md` (direto ou por grupo rastreável).

## Épicos filhos

1. **Contrato e autenticação** — Sanctum (tokens/abilities) por perfil (responsável, professor), refresh, LGPD (consentimento, exclusão).
2. **API família (5.13)** — alunos vinculados, frequência/desempenho, cardápio, fila/pré-matrícula, rematrícula, comunicados, transporte (quando houver).
3. **API professor (5.14)** — turmas, chamada, notas, planejamento, comunicados, sync/offline (fila de eventos), histórico funcional resumido.
4. **Push e webhooks** — registro de device, tópicos, entrega de notificações para o app consumir.
5. **OpenAPI + sandbox** — especificação publicada, exemplos, ambiente de homologação para a equipe mobile.
6. **Gate PoC** — endpoints operacionais cobrindo ≥ 85% dos requisitos de dados dos módulos 5.13/5.14; a demo de loja fica a cargo do time de apps.

## Critérios de aceite (PoC)

- [ ] Checklist [`REQUISITOS-TR.md`](./REQUISITOS-TR.md) com **100%** dos itens classificados (atende / gap / N/A)
- [ ] Itens **atende** demonstráveis na PoC (≥ 85% do módulo 5.13 e 5.14)
- [ ] Itens **gap** ≤ 15% com plano ≤ 30 dias em produção, sem ônus
- [ ] Nenhum requisito do TR omitido da matriz


## Fora de escopo deste EPIC

- Código Android/iOS, design de telas, contas de desenvolvedor das lojas e review das stores.
- Substituição do i-Diário web; o app professor **consome** as mesmas regras via API.
- Orçamento comercial da proposta Laguna.

## Dependências de domínio

- Alimentação (5.2) → cardápio.
- Fila/pré-matrícula (5.8) → `portabilis/pre-matricula-digital` (adaptado; sem pacote Buriti de fila).
- Escolar/i-Diário (5.3/5.14) → diário, notas, chamada.
- Transporte v2 (5.4) → status de rota/embarque quando no escopo do app família.
- Servidor (5.1) → histórico funcional resumido no app professor.
- Atendimento (5.9) → agendamento/confirmação quando no escopo.
