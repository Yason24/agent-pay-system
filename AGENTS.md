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
