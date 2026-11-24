# Roadmap — App Android ( Lagunas / PE-77/2026 )

**Backend:** `buriti/i-educar-apps-api-package` (`api/apps/v1`)  
**UI nativa / lojas:** equipe externa (`5.13-LOJA` / `5.14-LOJA`) — este documento é o contrato de handoff.

## Princípios

1. Um único backend Sanctum para **pais/responsáveis** e **professores**.  
2. Offline-first no professor (fila `POST /professor/sync` idempotente).  
3. Domínio vive nos pacotes Buriti / Portabilis / i-Diário — a API **orquestra**.  
4. OpenAPI gerada a partir do catálogo (`GET /api/apps/v1/catalogo`).

## Fases

### A0 — Fundação (1–2 sprints)

| Item | Dependência | Status API |
| --- | --- | --- |
| Login / logout Sanctum | núcleo | contrato |
| Catálogo + health | apps-api | **ok** |
| Build flavors (homolog/prod) | mobile | handoff |

### A1 — Família core (Onda 1)

| Tela Android | Endpoint | Módulo |
| --- | --- | --- |
| Meus filhos | `GET /familia/filhos` | 5.3 |
| Frequência | `GET /familia/filhos/{id}/frequencia` | 5.3 / i-Diário |
| Notas | `GET /familia/filhos/{id}/notas` | 5.3 / i-Diário |
| Fila / protocolo | `GET /familia/fila` | 5.8 |
| Transporte | `GET /familia/transporte` | 5.4 |
| Uniforme | `GET /familia/uniforme` | 5.3 |

### A2 — Família Onda 2

| Tela | Endpoint | Módulo |
| --- | --- | --- |
| Cardápio do dia | `GET /familia/cardapio` | 5.2 |
| Atendimentos | `GET /familia/atendimentos` | 5.9 |
| Biblioteca | `GET /familia/biblioteca` | 5.5 |

### A3 — Professor

| Tela | Endpoint | Módulo |
| --- | --- | --- |
| Turmas | `GET /professor/turmas` | i-Diário |
| Chamada | `POST /professor/chamada` + sync | i-Diário / 5.14 |
| Notas | `POST /professor/notas` | i-Diário |
| Sync offline | `POST /professor/sync` | 5.14 |
| Histórico funcional | `GET /professor/historico-funcional` | 5.1 |
| Horário | `GET /professor/horario` | 5.11 |
| Eventos/formação | `GET /professor/eventos` | 5.12 |

### A4 — Publicação lojas

- Pacotes Android/iOS, ícones, privacidade, deep links de protocolo/fila.  
- Sandbox de handoff + Postman/OpenAPI congelada.  
- Evidências PoC ≥85% dos itens de **dados** 5.13/5.14.

## Critérios de aceite mobile

- [ ] Login responsável lista filhos  
- [ ] Frequência/notas batem com i-Diário/homolog  
- [ ] Protocolo de fila consultável  
- [ ] Cardápio do dia (com restrição)  
- [ ] Professor lança chamada offline e sincroniza  
- [ ] Histórico funcional resumido  

## Referências

- Catálogo runtime: `GET /api/apps/v1/catalogo`  
- `docs/onda-2/` dos pacotes de domínio  
- i-Diário: https://github.com/i-Educar/i-diario  
