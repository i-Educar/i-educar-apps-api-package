# Apps API (família e professor)

Backend único (Laravel Sanctum) que expõe, em contrato versionado, os dados e operações necessários aos apps mobile de responsáveis e professores.

Composer: `buriti/i-educar-apps-api-package`

## Para que serve

Este pacote **não** inclui UI mobile nem publicação em lojas. Entrega a API estável para que outra equipe construa Android/iOS/PWA, com governança SME (suspender usuário, bloquear município, tokens).

## Desenho de funções

| Função | Entrega |
| --- | --- |
| Autenticação | Login/logout, perfil (família/professor), dispositivos e status |
| Catálogo de capacidades | Descoberta do que o município liberou no app |
| Família | Filhos, frequência, notas, cardápio, transporte, fila, uniforme, atendimento, biblioteca, busca ativa |
| Professor | Dados e operações do diário / rotina docente via API |
| Governança SME | Suspender/reativar usuário, revogar token, bloquear/liberar município |
| OpenAPI | Contrato em `docs/openapi-apps-v1.yaml` |

## Quem usa

- Equipe mobile (consumidora da API)
- SME (governança)
- Famílias e professores (via app)

## Integrações

- Alimentação, transporte, busca ativa, atendimento, biblioteca, pré-matrícula, i-Diário/núcleo escolar
- Sanctum no i-Educar

## Compatibilidade

| Produto | Versão |
| --- | --- |
| **i-Educar** | **2.12.x** |
| **i-Diário** | Portabilis i-Diário (API / SSO do núcleo) |
| Pacote | `0.6.1` |

Declarado em `composer.json` → `extra.ieducar` / `extra.idiario`.

## Documentação

| Documento | Uso |
| --- | --- |
| [`EPIC.md`](./EPIC.md) | Escopo de produto, aceite e critérios |
| [`ROADMAP.md`](./ROADMAP.md) | Fases e entregas |
| [`TODO.md`](./TODO.md) | Backlog operacional |
| [`docs/openapi-apps-v1.yaml`](./docs/openapi-apps-v1.yaml) | Contrato OpenAPI v1 |
| [`docs/ROADMAP-ANDROID.md`](./docs/ROADMAP-ANDROID.md) | Orientação para time mobile |
| [`REQUISITOS-TR.md`](./REQUISITOS-TR.md) | Rastreio de requisitos (planejamento) |

---

Licença: **GPL-2.0-or-later** · Organização [i-Educar](https://github.com/i-Educar)
