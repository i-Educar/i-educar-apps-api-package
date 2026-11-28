# ROADMAP — APIs para Apps (família e professor)

> Pacote: `buriti/i-educar-apps-api-package` · TR **5.13 + 5.14** · Prioridade **P0** · Nota **D/C** (lojas)  
> Alinhado a `packages/buriti/docs/laguna/DOC-EXECUTIVO-PE-77-2026-LAGUNA-IEDUCAR.md`

## Visão

Um backend de APIs versionado que alimenta os apps de **pais/responsáveis** e **professores**. A interface mobile é construída por **outra equipe**, em repositório e pipeline próprios; este pacote entrega contrato, segurança e dados.

```text
i-Educar + pacotes de domínio
        ↓
 i-educar-apps-api-package  (este)
        ↓  OpenAPI / tokens / push
 Apps Android · iOS  (equipe externa)
```

## Fases

### F0 · Contrato e governança

Definir perfis (responsável, professor), escopos Sanctum token, versionamento (`/api/apps/v1`), política de breaking changes e donos de cada recurso (qual pacote de domínio é a fonte).

### F1 · Fundação

Auth, refresh, vínculo responsável↔aluno e professor↔turma, rate limit, logs de acesso, esqueleto OpenAPI e ambiente de homologação.

### F2 · Fatia família (5.13)

Endpoints: filhos/vínculos, frequência e desempenho resumido, cardápio, fila/pré-matrícula, rematrícula, comunicados; stubs claros onde o domínio ainda não existe (ex.: cardápio até 5.2 MVP).

### F3 · Fatia professor (5.14)

Endpoints: turmas, chamada, notas, planejamento, comunicados, sync (pull/push de eventos offline), histórico funcional resumido (5.1).

### F4 · Push + PoC de API

Registro de devices, tópicos, carga de notificação; bateria de testes de contrato; handoff formal à equipe mobile com sandbox e coleção Postman/Insomnia.

### F5 · 30 dias pós-PoC

Fechar ≤ 15% de endpoints/campos faltantes sem ônus, sem alterar o contrato de forma incompatível.

## Cobertura 100% dos requisitos (TR)

Fonte: [`REQUISITOS-TR.md`](./REQUISITOS-TR.md) — **48 itens**.

Cada lote deve sair da PoC como **atende** ou entrar no plano de 30 dias.

### Lote R1 — itens 5.13.1 … 5.13.18 (15)

- `5.13.1`, `5.13.2`, `5.13.3`, `5.13.4`, `5.13.5`, `5.13.6`, `5.13.7`, `5.13.8`, `5.13.9`, `5.13.10`, `5.13.13`, `5.13.15`, `5.13.16`, `5.13.17`, `5.13.18`

- [ ] Implementar / validar / evidenciar na matriz PoC

### Lote R2 — itens 5.13.19 … 5.14.3 (15)

- `5.13.19`, `5.13.20`, `5.13.21`, `5.13.22`, `5.13.23`, `5.13.24`, `5.13.25`, `5.13.26`, `5.13.27`, `5.13.28`, `5.13.29`, `5.13.30`, `5.14.1`, `5.14.2`, `5.14.3`

- [ ] Implementar / validar / evidenciar na matriz PoC

### Lote R3 — itens 5.14.4 … 5.14.20 (15)

- `5.14.4`, `5.14.5`, `5.14.6`, `5.14.7`, `5.14.8`, `5.14.9`, `5.14.10`, `5.14.12`, `5.14.13`, `5.14.15`, `5.14.16`, `5.14.17`, `5.14.18`, `5.14.19`, `5.14.20`

- [ ] Implementar / validar / evidenciar na matriz PoC

### Lote R4 — itens 5.14.21 … 5.14.23 (3)

- `5.14.21`, `5.14.22`, `5.14.23`

- [ ] Implementar / validar / evidenciar na matriz PoC

### Gate 100%

- [ ] Todos os lotes R* com status atende ou gap≤30dias
- [ ] Soma atende ≥ 85% na demonstração

## Conformidade Laravel / pacote i-Educar 2.12

Antes da fase de código:

1. Criar `composer.json` com `php >=8.3`, `illuminate/support`, provider em `extra.laravel`, `compatible: ["2.12"]`.
2. Scaffold: `src/Providers`, `Http/Controllers`, `Services`, `Support`, `database/migrations`, `routes/web.php`, views Blade.
3. Schema `apps_api (apenas metadados: devices, push, refresh) — dados de domínio ficam nos pacotes fontes` (ou estratégia documentada); FKs lógicas para núcleo (`ref_cod_aluno`, `ref_cod_escola`, etc.).
4. Menu/processos em migration própria; respeitar multi-tenant.
5. Não acoplar UI mobile; dados para lojas só via ``buriti/i-educar-apps-api-package``.
6. Seguir `packages/buriti/PADRAO-PACOTE-2.12.md`.

## Dependências

- Domínios: escolar/i-Diário, pré-matrícula, alimentação, transporte, servidor, atendimento.
- Exportação unificada (4.x) só se o app exigir downloads de documentos nos formatos do TR.
- Contas e publicação nas lojas: **fora** deste roadmap (equipe de apps).

## Riscos

- Atraso nos pacotes de domínio (ex.: cardápio) bloqueia campos da API — usar contratos com `null`/feature flags documentados.
- Sync offline do professor é o maior risco técnico do 5.14 no backend.
- PoC do edital exige app **nas lojas**; o sucesso da API sozinho não fecha 5.13/5.14 — depende do time mobile.

## Definição de pronto da fase PoC (este pacote)

OpenAPI da fatia PoC publicada, homologação estável, checklist do `TODO.md` da fatia API concluído e handoff assinado com a equipe de apps.
