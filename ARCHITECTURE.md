# Architecture

Agent Pay System is a custom PHP MVC framework inspired by Laravel.

## Request Lifecycle

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
