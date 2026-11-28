# Documento técnico — App Android unificado (Laguna / PE-77/2026)

**Destinatário:** equipe de desenvolvimento Android / iOS (lojas)  
**Backend:** `buriti/i-educar-apps-api-package` · base `{IEDUCAR_HOST}/api/apps/v1`  
**Data:** 2026-11-26 · **Onda 10/11** · handoff reforçado na **Onda 17**  
**Objetivo:** um único aplicativo com três experiências (perfis Sanctum), reutilizando a mesma instalação, autenticação e catálogo.

> **Escopo Buriti:** este documento + a API (`/api/apps/v1`, OpenAPI, catálogo) são a entrega.  
> **Não** há implementação nativa Android/iOS neste monorepo. Build, FCM, CameraX e publicação nas lojas ficam com a equipe externa (`5.13-LOJA` / `5.14-LOJA`).  
> Gate PoC Buriti: `packages/buriti/docs/laguna/GATE-100-POC.md` (WhatsApp e APK fora).

---

## 1. Visão do produto

| Perfil (`perfil` no login) | Público | TR âncora | Pacotes de domínio |
| --- | --- | --- | --- |
| `familia` | Pais / responsáveis | **5.13** | Escolar, pré-matrícula, transporte, alimentação, atendimento, biblioteca, busca ativa |
| `professor` | Docentes | **5.14** | i-Diário + horário + eventos + RH resumido |
| `equipe` | Equipe técnica SME/escola | **5.7 / 5.5 / 5.15** (+ ops) | Almoxarifado (QR, etiquetas, estoque), biblioteca (empréstimos), contratações (ACT) |
| `admin` | Governança SME | 5.13/5.14 ops | Suspender usuário, bloquear município |

UI nativa, push FCM e publicação nas lojas **não** estão neste monorepo (`5.13-LOJA` / `5.14-LOJA`). A API entrega dados, operações e contratos estáveis.

```text
┌─────────────────────────────────────────────┐
│           App Android (único APK/AAB)         │
│  Bottom nav / drawer por perfil do token     │
├──────────┬──────────┬───────────┬────────────┤
│ Família  │Professor │  Equipe   │ Admin*     │
└────┬─────┴────┬─────┴─────┬─────┴──────┬─────┘
     │          │           │            │
     └──────────┴───── Sanctum Bearer ───┘
                      │
              /api/apps/v1/*
                      │
     ┌────────────────┼────────────────┐
     ▼                ▼                ▼
  Pacotes Buriti   i-Diário SSO    Exportação 4.x
```

\* Admin pode ser tela oculta (deep link / flavor SME).

---

## 2. Fundação (obrigatório antes de telas)

### 2.1 Ambientes

| Flavor | Base URL (exemplo) | Observação |
| --- | --- | --- |
| `homolog` | `https://homolog.municipio.gov.br` | Seeds Laguna / `laguna:setup` |
| `prod` | URL oficial SME | `APPS_API_ENABLED=true` |

Sempre consultar **antes do login**:

`GET /api/apps/v1/auth/status` → `apps_habilitados`, `perfis[]`, `features`, `indisponiveis[]`.

Se `apps_habilitados=false` → tela de município suspenso (mensagem da API).

### 2.2 Login

```http
POST /api/apps/v1/auth/login
Content-Type: application/json

{
  "login": "matricula_portal",
  "password": "***",
  "perfil": "familia|professor|equipe|admin",
  "device_name": "android-pixel-8-abc123"
}
```

Resposta: token Sanctum; ability do token = perfil.  
Middleware rejeita cross-perfil (403 `Perfil insuficiente`).

`GET /auth/me` · `POST /auth/logout` · `POST /auth/logout-all`

### 2.3 Catálogo vivo

`GET /api/apps/v1/catalogo` (Bearer) — lista endpoints reais + `cobertura_tr`.  
OpenAPI: `GET /api/apps/v1/openapi.yaml` (público).

**Regra:** não inventar paths no app. Se não estiver no catálogo, não chamar.

### 2.4 Segurança

- TLS only · Bearer no `Authorization`
- Timeout 30s · retry idempotente só em GET e em `POST /professor/sync`
- Ao 401: limpar token e voltar ao login
- Ao 403 `user_suspended`: mensagem fixa + logout
- Ao 503 `apps_api_disabled` / `apps_feature_disabled`: respeitar `indisponivel`/`impacto`

---

## 3. Mapa de telas × endpoints

### 3.1 Família (`perfil=familia`) — TR 5.13

| Tela Android | Método | Path | Notas |
| --- | --- | --- | --- |
| Meus filhos | GET | `/familia/filhos` | PII · escopo responsável |
| Frequência | GET | `/familia/filhos/{aluno}/frequencia` | 403 se não vinculado |
| Notas / boletim | GET | `/familia/filhos/{aluno}/notas` | |
| Fila / protocolo | GET | `/familia/fila` | 5.8 |
| Transporte | GET | `/familia/transporte` | 5.4 |
| Cardápio do dia | GET | `/familia/cardapio?data=YYYY-MM-DD` | 5.2 · restrições |
| Uniforme | GET | `/familia/uniforme` | |
| Atendimentos | GET | `/familia/atendimentos` | 5.9 |
| Biblioteca (consulta) | GET | `/familia/biblioteca` | 5.5 |
| Busca ativa · processos | GET | `/familia/busca-ativa/processos` | |
| Busca ativa · detalhe | GET | `/familia/busca-ativa/processos/{id}` | |
| Busca ativa · retorno | POST | `/familia/busca-ativa/processos/{id}/acompanhamento` | |
| Alertas do filho | GET | `/familia/filhos/{aluno}/busca-ativa/alertas` | |

**UX:** seletor de filho no topo; deep link `ieducar://fila/{protocolo}` quando houver.

### 3.2 Professor (`perfil=professor`) — TR 5.14

| Tela | Método | Path | Notas |
| --- | --- | --- | --- |
| Minhas turmas | GET | `/professor/turmas?ano=2026` | |
| Chamada | POST | `/professor/chamada` | Offline → fila local |
| Notas | POST | `/professor/notas` | |
| Sync offline | POST | `/professor/sync` | Idempotente (`client_id`) |
| Histórico funcional | GET | `/professor/historico-funcional` | 5.1 |
| Horário | GET | `/professor/horario` | 5.11 |
| Eventos / formação | GET | `/professor/eventos` | 5.12 |
| Health i-Diário | GET | `/diario/status` | Banner se indisponível |

**Offline-first:** Room/SQLite com fila de sync; ao reconectar chamar `/professor/sync` com lote. Não duplicar lançamentos (chave idempotente).

### 3.3 Equipe técnica (`perfil=equipe`) — Onda 10

Perfil para **almoxarife, bibliotecário, apoio SME** no mesmo APK.

| Tela / fluxo | Método | Path | Hardware / UX |
| --- | --- | --- | --- |
| Painel equipe | GET | `/equipe/painel` | Atalhos + módulos disponíveis |
| Pedidos / notas | GET | `/equipe/almox/pedidos` | Lista |
| Detalhe pedido | GET | `/equipe/almox/pedidos/{id}` | Link `nota_url` / `pdf_url` (WebView ou Custom Tabs) |
| Emitir QR remessa | GET | `/equipe/almox/pedidos/{id}/qr` | Mostrar QR de `url_confirmacao` / `payload_scan` |
| **Ler QR → confirmar** | POST | `/equipe/almox/confirmar` body `{ "token": "..." }` | CameraX ML Kit / ZXing · token = path final da URL ou campo `token` |
| Etiquetas | GET | `/equipe/almox/pedidos/{id}/etiquetas` | Impressora térmica / PDF WebView `print_url` |
| Saldos / baixa visual | GET | `/equipe/almox/saldos` | Alertar saldo ≤ mínimo |
| Empréstimos biblioteca | GET | `/equipe/biblioteca/emprestimos` | |
| Processos ACT | GET | `/equipe/contratacoes/processos` | Abrir `url` web se inscrição pública |

#### Fluxo QR (5.7.3) — obrigatório na PoC

1. Usuário abre **Ler QR**.  
2. Scanner lê URL `…/almoxarifado-escolar/confirmar-recebimento/{token}` **ou** o JSON/`token` retornado por `/equipe/almox/pedidos/{id}/qr`.  
3. Extrair `{token}` (último segmento da URL).  
4. `POST /equipe/almox/confirmar` com `{ "token": "..." }`.  
5. Feedback: sucesso → detalhe do `pedido_id`; erro → mensagem da API.

Baixa de estoque formal continua no fluxo de **assinatura de entrega** da nota (web/SME); o QR confirma **recebimento** da remessa. O app deve abrir a nota PDF institucional quando o operador precisar das assinaturas:

`GET` web `/almoxarifado-escolar/pedidos/{id}/nota/pdf` (sessão web) **ou** Custom Tab autenticada conforme política da SME.

#### Etiquetas

Consumir array `etiquetas[]` (`sku`, `titulo`, `quantidade`, `codigo_barras`, `escola`, `pedido_id`).  
Impressão: layout 50×30 / 60×40 mm; fallback `print_url` no Chrome Custom Tabs.

### 3.4 Admin (`perfil=admin`)

| Operação | Path |
| --- | --- |
| Status governança | `GET /admin/governanca` |
| Suspender / reativar | `POST /admin/usuarios/{id}/suspender` · `…/reativar` |
| Bloquear / liberar município | `POST /admin/municipio/bloquear` · `…/liberar` |

---

## 4. Assinatura digital (LibreSign) — handoff

Pacote: `i-educar-assinatura-digital-package` (TR **5.3.87**).  
No Android **não** implementar assinatura nativa nesta fase:

1. Receber push / deep link `ieducar://assinatura/{pedidoId}` (quando push estiver ligado).  
2. Abrir **WebView/Custom Tabs** na URL de assinatura web do LibreSign / i-Educar.  
3. Após retorno, atualizar lista (endpoint futuro `/equipe/assinaturas/pendentes` — reservado).

---

## 5. Modelo de navegação sugerido (Material 3)

```text
Splash → auth/status
      → Login (escolhe perfil se multi-elegível)
      → Home por perfil:
           Família: Filhos | Escola | Cardápio | Mais
           Professor: Turmas | Chamada | Horário | Sync
           Equipe: Operar (QR) | Pedidos | Biblioteca | ACT
```

Deep links sugeridos:

| Scheme | Uso |
| --- | --- |
| `ieducar://familia/filho/{id}` | Abrir filho |
| `ieducar://fila/{protocolo}` | Protocolo pré-matrícula |
| `ieducar://equipe/qr` | Abrir scanner |
| `ieducar://equipe/pedido/{id}` | Pedido almox |
| `ieducar://assinatura/{id}` | Assinar na web |

---

## 6. Dados sensíveis (LGPD)

- Marcar telas PII no catálogo (`pii: true`).
- Sem log de corpo de frequência/notas/atendimentos.
- Screenshot block opcional em notas/atendimento (flag remote config).
- Cache local cifrado (SQLCipher ou EncryptedSharedPreferences) no professor offline.

---

## 7. Checklist de aceite (PoC mobile)

### Família
- [ ] Login lista filhos  
- [ ] Frequência/notas coerentes com homolog  
- [ ] Protocolo de fila consultável  
- [ ] Cardápio do dia com restrição  
- [ ] Biblioteca e transporte renderizam  

### Professor
- [ ] Turmas do servidor  
- [ ] Chamada offline + sync sem duplicar  
- [ ] Histórico funcional / horário / eventos  

### Equipe
- [ ] Login `equipe` com funcionário ativo  
- [ ] Lista pedidos almox  
- [ ] Scanner confirma QR (`POST …/almox/confirmar`)  
- [ ] Etiquetas retornam SKUs  
- [ ] Empréstimos e processos ACT listam  

### Plataforma
- [ ] Município bloqueado → 503 tratado  
- [ ] Feature desligada → mensagem SME  
- [ ] OpenAPI / catálogo batem com o app  

---

## 8. Artefatos no repositório

| Artefato | Caminho |
| --- | --- |
| Este documento | `i-educar-apps-api-package/docs/DOC-TECNICO-APP-ANDROID-UNIFICADO.md` |
| Roadmap curto | `docs/ROADMAP-ANDROID.md` |
| Handoff 5.13/5.14 | `docs/onda-3/HANDOFF-5.13-5.14.md` |
| OpenAPI | `docs/openapi-apps-v1.yaml` |
| Status Onda 10 | `packages/buriti/docs/onda-10/STATUS.md` |
| Governança Onda 5 | `docs/onda-5/STATUS.md` |

---

## 9. Fora de escopo (explícito)

- Publicação Play Console / App Store Connect  
- Design system pixel-perfect da SME (usar Material 3 + cores Buriti `#0a7a8a` como referência)  
- Reimplementar motor i-Diário no APK  
- Assinatura criptográfica nativa (usar LibreSign web)  

---

**Contato backend:** pacote `buriti/i-educar-apps-api-package` · org i-Educar.  
**Sandbox:** instância Laguna + usuário `portal.funcionario` + vínculos família/professor/equipe conforme handoff Onda 3/10.
