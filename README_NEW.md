# Agent Pay System

Laravel-inspired PHP framework and application foundation for a future payment platform.

## Current Stage

The repository is in the framework-first phase.

Implemented today:

- Service container
- Dependency injection
- HTTP kernel
- Router facade
- Middleware pipeline
- Basic view system with Blade-like directives
- Model, query builder, migrations, and seed scaffolding

## Project Direction

This repository is not a generic website template.

The goal is to grow a reusable application platform with clear Laravel DNA:

- clean application bootstrap
- service-oriented architecture
- controller-based request flow
- reusable middleware and providers
- progressive move into auth, dashboard, and payment modules

## Run Flow

Request flow:

`public/index.php -> Application -> HTTP Kernel -> Middleware -> Router -> Controller -> View -> Response`

## Next Phase

Before LEVEL 3, complete a short hardening pass:

1. stabilize routing and request dispatch
2. finalize base controller behavior
3. make layouts and sections work consistently
4. align repository metadata with the actual framework identity

After that, LEVEL 3 should start with:

1. auth foundation
2. user model and sessions
3. dashboard shell
4. payment domain boundaries

