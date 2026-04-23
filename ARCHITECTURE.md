# Architecture

Agent Pay System is a custom PHP MVC framework inspired by Laravel.

## Request   Lifecycle

```text
public/index.php
-> bootstrap/app.php
-> Framework\Core\Application
-> Framework\Core\Http\Kernel
-> global middleware
-> Framework\Core\Router
-> route middleware
-> controller
-> view or   response
```

## Main Layers

- `app/` contains application controllers, middleware, models, providers, and services.
- `framework/` contains the custom framework core.
- `routes/web.php` registers web routes through the `Route` facade.
- `resources/views/` contains PHP views compiled by the Blade-like view layer.
- `migrations/` and `seeders/` contain database setup code.
- `public/index.php` is the only web entry point.

## Core Components

- `Application` extends the container and registers base services/providers.
- `Http\Kernel` runs middleware and dispatches requests.
- `Router` registers routes, groups, middleware aliases, and controller actions.
- `Pipeline` runs middleware around the request.
- `ViewFactory` renders views and layouts.
- `Database`, `Model`, and `QueryBuilder` provide the current PDO/model layer.

## Database

The current database target is PostgreSQL. Runtime DB values come from `.env`; `.env.example` documents the expected keys.

## Domain Architecture

### Core Rule

Agent = user with role `agent`.

The system must not treat `agent` as a separate primary domain entity.

### Main Domains

- Users
- Legal entities
- Agent balances
- Payment requests
- Payments
- Debt operations
- Audit logs
- Notifications

### Ownership Model

All agent-owned data must be linked to `users.id`.

Examples:
- balances -> `agent_user_id`
- payment requests -> `agent_user_id`
- debt operations -> `agent_user_id`
- reports/filters -> based on agent user account

### Access Model

- `agent` can access only own data
- `dispatcher` can process operational requests
- `accountant` can manage accruals and corrections
- `admin` can manage users, roles, legal entities and logs

### Financial Integrity

Balance must not be edited as free-form state.
Financial changes must be derived from operations history and protected by transactions where needed.