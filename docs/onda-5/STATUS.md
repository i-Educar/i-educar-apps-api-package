# Onda 5 — Governança auth apps-api

## Comportamento Android

1. App chama `GET /auth/status` (público, ainda sujeito a 503 se município bloqueado via middleware… — status fica **dentro** do middleware enabled; se bloqueado, 503).
2. Login: `POST /auth/login` com `{login, password, perfil, device_name}` (device_name **obrigatório**).
3. Todas as demais rotas de dados exigem `Authorization: Bearer …`.
4. Se admin suspender o usuário → tokens Sanctum e sessões web apagados; próximas requests → 403 `user_suspended`.
5. Se município for desligado → 503 `apps_api_disabled` em toda a API apps (alinha `active_on_ieducar`).

## Admin HTTP (perfil `admin` + `isAdmin()`)

- `POST /admin/usuarios/{id}/suspender`
- `POST /admin/usuarios/{id}/reativar`
- `POST /admin/municipio/bloquear`
- `POST /admin/municipio/liberar`
