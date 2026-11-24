# Handoff formal — Apps Família (5.13) e Professor (5.14)

**Destinatário:** equipe de lojas / app nativo (`5.13-LOJA` / `5.14-LOJA`)  
**Fornecedor API:** pacote `buriti/i-educar-apps-api-package` · org [i-Educar](https://github.com/i-Educar/i-educar-apps-api-package)

## Base URL

`{IEDUCAR_HOST}/api/apps/v1`

## Auth

1. `POST /auth/login` com `{ login, password, perfil: "familia"|"professor" }`
2. Usar `Authorization: Bearer {token}` nas rotas protegidas
3. Ability do token = perfil; middleware rejeita cross-perfil (403)

## Spec

- OpenAPI 3: [`../openapi-apps-v1.yaml`](../openapi-apps-v1.yaml)
- Catálogo vivo: `GET /catalogo` (inclui `cobertura_tr`)
- Roadmap Android: [`../ROADMAP-ANDROID.md`](../ROADMAP-ANDROID.md)

## Matriz mínima (PoC)

| Perfil | Operação | Método | Path |
| --- | --- | --- | --- |
| ambos | login | POST | `/auth/login` |
| família | filhos | GET | `/familia/filhos` |
| família | frequência | GET | `/familia/filhos/{id}/frequencia` |
| família | notas | GET | `/familia/filhos/{id}/notas` |
| família | cardápio | GET | `/familia/cardapio?data=YYYY-MM-DD` |
| família | transporte | GET | `/familia/transporte` |
| família | fila | GET | `/familia/fila` |
| professor | turmas | GET | `/professor/turmas?ano=2026` |
| professor | chamada | POST | `/professor/chamada` |
| professor | notas | POST | `/professor/notas` |
| professor | sync offline | POST | `/professor/sync` |
| professor | histórico | GET | `/professor/historico-funcional` |

## Sandbox

1. Instância Laguna com pacote wired + Sanctum (`personal_access_tokens`)
2. Usuário `portal.funcionario` ativo (login/senha do núcleo)
3. Para família: pessoa do usuário como `idpes_responsavel`/`mae`/`pai` em `cadastro.fisica` de alunos
4. Para professor: linhas em `modules.professor_turma` com `servidor_id` = `cod_usuario`

## Fora do escopo desta API

- Publicação nas lojas Google/Apple
- Push FCM/APNs (hook reservado; implementação Onda 4+)
- UI web dos módulos de domínio
