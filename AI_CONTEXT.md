# AI Context - Agent Pay System

## Project

Agent Pay System is a custom Laravel-inspired PHP MVC framework and application foundation.

Do not rebuild the framework from scratch. Continue the existing structure.

## Current Runtime

Request flow:

```text
public/index.php -> bootstrap/app.php -> Application -> HTTP Kernel -> Middleware -> Router -> Controller -> View/Response
```

Implemented areas:

- PSR-4 Composer autoloading
- `Framework\Core\Application` container
- HTTP kernel and middleware pipeline
- Route facade and route groups
- Controllers and dependency injection
- Blade-like PHP views with layouts and sections
- PostgreSQL PDO connection through `.env`
- Models, query builder, migrations, and seeders
- Basic auth screens and protected dashboard route

## Current Routes

- `/`
- `/login`
- `/register`
- `/forgot-password`
- `/dashboard`
- `/logout`

## Environment

PostgreSQL is the current database target. Use `.env.example` as the local setup template.

Required DB keys:

- `DB_DRIVER=pgsql`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

## Documentation Rules

- Keep `README.md` as the single source of truth for first run instructions.
- Keep architecture docs short and tied to current code.
- Do not add duplicate roadmap or planning docs.
