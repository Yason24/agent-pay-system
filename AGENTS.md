# Agent Notes

Use this file as short orientation for coding agents.

## Project Shape

- Custom Laravel-inspired PHP MVC framework
- Entry point: `public/index.php`
- Bootstrap: `bootstrap/app.php`
- Framework core: `framework/`
- App code: `app/`
- Views: `resources/views/`
- Routes: `routes/web.php`
- Database target: PostgreSQL

## Mandatory Business Rule

Agent = user with role `agent`.

Do not model agent as a separate primary business entity.

- agents log in with their own credentials
- agents see only their own data
- agent-related entities must reference `users.id`
- use `agent_user_id` / `user_id` semantics, not standalone `agents.id` semantics

## Working Rules

- Prefer existing framework patterns
- Keep `README.md` as setup source of truth
- Keep `ARCHITECTURE.md` aligned with real architecture
- Do not add duplicate planning markdown files
- Do not reintroduce legacy `agents` business model

## UI Terminology (locked, do not change)

| Concept | UI label | Notes |
|---|---|---|
| `/payments` section | **Начисления** | Page title, nav links, empty state |
| Request status `paid` | **Оплачено** | Status label inside requests table only |
| Backend `payment.status` values | `pending`, `paid`, `оплачено` | Keep as-is, never rename in DB/logic |

Rules:
- All nav links to `/payments` or `/my/payments` must say `Начисления`
- Empty state on `/payments`: `Начислений пока нет.`
- `requests/index.php` `$statusLabel` map: `'paid' => 'Оплачено'` — correct, do not rename
- Backend comparisons like `['paid', 'оплачено']` — correct, do not rename
- Do NOT use "Оплачено" as a payments section label anywhere in UI
