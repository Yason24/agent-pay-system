# Agent Notes

Use this file as short project orientation for coding agents.

## Project Shape

- Custom PHP MVC framework inspired by Laravel.
- Entry point: `public/index.php`.
- Bootstrap: `bootstrap/app.php`.
- Framework core: `framework/`.
- Application code: `app/`.
- Views: `resources/views/`.
- Routes: `routes/web.php`.
- Database target: PostgreSQL.

## Current Request Flow

```text
public/index.php -> Application -> HTTP Kernel -> Middleware -> Router -> Controller -> View/Response
```

## Working Rules

- Prefer existing framework patterns over new abstractions.
- Keep `README.md` as the source of truth for setup and launch.
- Keep docs short and tied to current code.
- Keep setup text aligned with the current PostgreSQL runtime.
- Do not add duplicate roadmap/planning markdown files.
