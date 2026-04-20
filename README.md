# Agent Pay System

Custom Laravel-inspired PHP MVC framework and application foundation.

## Requirements

- PHP 8.1+
- Composer
- PostgreSQL
- PHP extension `pdo_pgsql`
- Web server with document root set to `public/`

## First Run

Install dependencies:

```bash
composer install
```

Create local environment file:

```bash
cp .env.example .env
```

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Edit `.env` if your PostgreSQL credentials differ from the example values.

Create the PostgreSQL database named in `DB_DATABASE`, then run migrations:

```bash
php console migrate
```

Start the app with PHP built-in server:

```bash
php -S 127.0.0.1:8000 -t public
```

For OSPanel, Apache, nginx, or Docker, point the site document root to:

```text
public/
```

## Local Checks

```bash
composer stability:smoke
composer stability:preflight
composer stability:check
```

`stability:preflight` and `stability:check` are OSPanel-oriented checks for PHP/PostgreSQL extension setup.

## Routes

- `GET /`
- `GET /login`
- `POST /login`
- `GET /register`
- `POST /register`
- `GET /forgot-password`
- `GET /dashboard`
- `POST /logout`

## Project Structure

```text
app/                 Application controllers, middleware, models, services, providers
bootstrap/app.php    Application bootstrap
config/              Framework config files
framework/           Custom framework core
migrations/          Database migrations
public/index.php     Front controller
resources/views/     PHP/Blade-like views
routes/web.php       Web routes
scripts/             Local stability checks
seeders/             Database seeders
```

## Runtime Flow

```text
public/index.php -> bootstrap/app.php -> Application -> HTTP Kernel -> Middleware -> Router -> Controller -> View/Response
```

## Notes

- PostgreSQL is the current database target.
- `.env` is local-only and must not be committed.
- Migration files should use the format expected by `MigrationRunner`: an array with `up` and `down` callbacks.
