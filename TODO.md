# TODO — APIs para Apps (família e professor)

> Pacote: `buriti/i-educar-apps-api-package` · TR **5.13 + 5.14** · Prioridade **P0**

## Backlog Laguna (backend / API)

- [ ] Congelar escopo OpenAPI v1 (família + professor) a partir do checklist OCR 5.13.x / 5.14.x
- [ ] Auth Sanctum: login, refresh/abilities por perfil, revogação
- [ ] Autorização: responsável só vê alunos vinculados; professor só turmas autorizadas
- [ ] Endpoints família: alunos, frequência, desempenho, comunicados
- [ ] Endpoints família: cardápio (depende 5.2) com contrato mesmo antes do MVP completo
- [ ] Endpoints família: fila / pré-matrícula / rematrícula (consome `portabilis/pre-matricula-digital`)
- [ ] Endpoints família: transporte (status) quando no escopo (depende 5.4)
- [ ] Endpoints professor: turmas, chamada, notas, planejamento
- [ ] Sync professor: fila de eventos offline, idempotência, conflitos
- [ ] Histórico funcional resumido no app professor (depende 5.1)
- [ ] Push: registro de device / tópicos / payload
- [ ] LGPD: consentimento, exportação/exclusão de dados do titular no canal app
- [ ] Sandbox de homologação + coleção Postman/Insomnia
- [ ] Documentação de handoff para a equipe mobile (ambientes, limites, versionamento)
- [ ] Testes de contrato (consumer-driven ou OpenAPI validation)

## Fora deste pacote (equipe de apps)

- [ ] UI Android / iOS (ou wrapper TWA/PWA) — repositório próprio
- [ ] Contas Google Play / Apple Developer e publicação nas lojas
- [ ] Builds de produção, ícones, políticas de loja, review
- [ ] Demo PoC em device real das lojas / TestFlight / Internal testing

## Scaffold 2.12 (quando iniciar código)

- [ ] `composer.json` (`buriti/…`, PHP ≥8.3, illuminate/support, `compatible: ["2.12"]`)
- [ ] `ServiceProvider` + `const PACKAGE`
- [ ] `routes/web.php` com middleware i-Educar
- [ ] Migrations no schema do domínio + guard `PackageTenant`
- [ ] Processos/menu (faixa própria)
- [ ] README + INSTALL espelhando transporte/setup
- [ ] Entrada futura em `packages/plug-and-play.json` (fora do ignore)
- [ ] Revisar `packages/buriti/PADRAO-PACOTE-2.12.md`

## Cobertura 100% do TR

- [ ] Percorrer 100% de [`REQUISITOS-TR.md`](./REQUISITOS-TR.md) (48 itens) e marcar atende/gap/N/A

- [ ] Abrir tarefas para **cada gap** (ou agrupar com IDs explícitos)

- [ ] Evidências de PoC por item atende

- [ ] Lista dos ≤15% com datas ≤30 dias

### Lotes (mesmo do ROADMAP)

- [ ] Lote R1: 5.13.1 … 5.13.18

- [ ] Lote R2: 5.13.19 … 5.14.3

- [ ] Lote R3: 5.14.4 … 5.14.20

- [ ] Lote R4: 5.14.21 … 5.14.23

## Gate PoC (API)

- [ ] Matriz 5.13.x / 5.14.x marcando o que é API vs UI
- [ ] ≥ 85% dos requisitos de **dados/operações** cobertos por endpoints operacionais
- [ ] Handoff à equipe mobile com ambiente estável
- [ ] Lista dos ≤ 15% faltantes (API) com plano ≤ 30 dias

## Referências

- `tmp/DOC-EXECUTIVO-PE-77-2026-LAGUNA-IEDUCAR.md`
- OCR: `/tmp/edital-ocr/p-01.txt` … `p-79.txt` (quando disponível)
- Pacotes de domínio em `packages/buriti/PE-77-2026-LAGUNA.md`
