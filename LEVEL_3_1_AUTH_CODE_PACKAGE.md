# Agent Pay System - LEVEL 3.1 Auth Code Package

Этот пакет не просто про "идею", а про реальный следующий слой кода.

Цель итерации:

1. session bootstrap
2. auth service
3. auth/guest middleware
4. login/logout flow
5. protected dashboard
6. route middleware support in Laravel-like style

## Что важно

Чтобы `LEVEL 3.1` работал нормально, нужно чуть расширить foundation:

- поддержка `POST` routes
- поддержка route middleware на уровне роутера и kernel
- redirect response

Без этого auth получится фрагментарным.

## 1. Replace `framework/Core/Http/Response.php`

```php
<?php

namespace Framework\Core\Http;

class Response
{
    public function __construct(
        protected mixed $content = '',
        protected int $status = 200,
        protected array $headers = []
    ) {}

    public static function redirect(string $location, int $status = 302): self
    {
        return new self('', $status, [
            'Location' => $location,
        ]);
    }

    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->status);

            foreach ($this->headers as $key => $value) {
                header("$key: $value");
            }
        }

        echo $this->content;
    }
}
```

## 2. Replace `framework/Core/Request.php`

```php
<?php

namespace Framework\Core;

class Request
{
    protected array $query;
    protected array $request;
    protected array $server;
    protected array $cookies;
    protected array $files;

    public function __construct(
        array $query = [],
        array $request = [],
        array $server = [],
        array $cookies = [],
        array $files = []
    ) {
        $this->query = $query;
        $this->request = $request;
        $this->server = $server;
        $this->cookies = $cookies;
        $this->files = $files;
    }

    public function input(string $key, $default = null)
    {
        return $this->request[$key]
            ?? $this->query[$key]
            ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->request);
    }

    public function only(array $keys): array
    {
        $data = [];

        foreach ($keys as $key) {
            $data[$key] = $this->input($key);
        }

        return $data;
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function isMethod(string $method): bool
    {
        return $this->method() === strtoupper($method);
    }

    public function uri(): string
    {
        return parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    }

    public static function capture(): static
    {
        return new static(
            $_GET,
            $_POST,
            $_SERVER,
            $_COOKIE,
            $_FILES
        );
    }
}
```

## 3. Replace `framework/Core/Router.php`

```php
<?php

namespace Framework\Core;

use Closure;
use Exception;
use Framework\Core\Http\Response;

class Router
{
    protected array $routes = [];
    protected array $groupStack = [];
    protected array $groupMiddleware = [];
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function get(string $uri, $action): void
    {
        $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, $action): void
    {
        $this->addRoute('POST', $uri, $action);
    }

    protected function addRoute(string $method, string $uri, $action): void
    {
        $routeMiddleware = [];

        if (!empty($this->groupStack)) {
            $prefix = '';

            foreach ($this->groupStack as $group) {
                if (isset($group['prefix'])) {
                    $prefix .= '/' . trim($group['prefix'], '/');
                }

                if (isset($group['middleware'])) {
                    $routeMiddleware = array_merge(
                        $routeMiddleware,
                        (array) $group['middleware']
                    );
                }
            }

            $uri = $prefix . '/' . ltrim($uri, '/');
        }

        $method = strtoupper($method);
        $uri = $this->normalizeUri($uri);

        $this->routes[$method][$uri] = [
            'action' => $action,
            'middleware' => $routeMiddleware,
        ];
    }

    public function middleware(string|array $middleware): self
    {
        $this->groupMiddleware = (array) $middleware;

        return $this;
    }

    public function prefix(string $prefix): self
    {
        return $this->group([
            'prefix' => $prefix,
        ], fn () => $this);
    }

    public function group($attributes, ?callable $callback = null): self
    {
        if ($attributes instanceof Closure) {
            $callback = $attributes;
            $attributes = [];
        }

        if (!empty($this->groupMiddleware)) {
            $attributes['middleware'] = array_merge(
                $attributes['middleware'] ?? [],
                $this->groupMiddleware
            );

            $this->groupMiddleware = [];
        }

        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);

        return $this;
    }

    public function match(string $method, string $uri): array
    {
        $method = strtoupper($method);
        $uri = $this->normalizeUri($uri);

        $route = $this->routes[$method][$uri] ?? null;

        if (!$route) {
            throw new Exception('Route not found');
        }

        return $route;
    }

    public function dispatch(string $method, string $uri)
    {
        $route = $this->match($method, $uri);
        $action = $route['action'];

        if ($action instanceof Closure) {
            return new Response($action());
        }

        if (is_array($action)) {
            [$controller, $methodName] = $action;
            $controller = $this->container->make($controller);

            return new Response($controller->$methodName());
        }

        throw new Exception('Invalid route action');
    }

    protected function normalizeUri(string $uri): string
    {
        $normalized = '/' . trim($uri, '/');

        return $normalized === '//' ? '/' : $normalized;
    }
}
```

## 4. Replace `framework/Core/Http/Kernel.php`

```php
<?php

namespace Framework\Core\Http;

use Framework\Core\Application;
use Framework\Core\Http\MiddlewareRegistry;
use Framework\Core\Pipeline;
use Framework\Core\Request;
use Framework\Core\Router;

class Kernel
{
    protected Application $app;
    protected Router $router;
    protected MiddlewareRegistry $registry;

    protected array $middleware = [
        \Framework\Core\Http\Middleware\TrustProxies::class,
        \Framework\Core\Http\Middleware\TrimStrings::class,
        \Framework\Core\Http\Middleware\StartSession::class,
    ];

    public function __construct($app)
    {
        $this->app = $app;
        $this->router = $app->make(Router::class);
        $this->registry = new MiddlewareRegistry();

        $this->registerMiddleware();
        $this->loadRoutes();
    }

    protected function pipeline(): Pipeline
    {
        return new Pipeline($this->app);
    }

    public function handle(Request $request)
    {
        $route = $this->router->match(
            $request->method(),
            $request->uri()
        );

        $middleware = array_merge(
            $this->middleware,
            $this->resolveMiddleware($route['middleware'] ?? [])
        );

        $response = $this->pipeline()
            ->send($request)
            ->through($middleware)
            ->then(fn (Request $request) => $this->router->dispatch(
                $request->method(),
                $request->uri()
            ));

        return $this->prepareResponse($response);
    }

    protected function prepareResponse($response): Response
    {
        if ($response instanceof Response) {
            return $response;
        }

        if (is_array($response)) {
            return new Response(
                json_encode($response),
                200,
                ['Content-Type' => 'application/json']
            );
        }

        return new Response((string) $response);
    }

    protected function loadRoutes(): void
    {
        $router = $this->app->make(\Framework\Core\Router::class);

        require $this->app->basePath('routes/web.php');
    }

    public function resolveMiddleware(array $middleware): array
    {
        return $this->registry->resolve($middleware);
    }

    protected function registerMiddleware(): void
    {
        $this->registry->alias(
            'trust',
            \Framework\Core\Http\Middleware\TrustProxies::class
        );

        $this->registry->alias(
            'web',
            \Framework\Core\Http\Middleware\WebMiddleware::class
        );

        $this->registry->alias(
            'auth',
            \App\Middleware\AuthMiddleware::class
        );

        $this->registry->alias(
            'guest',
            \App\Middleware\GuestMiddleware::class
        );

        $this->registry->group('web', [
            'trust',
            \Framework\Core\Http\Middleware\WebMiddleware::class,
        ]);
    }
}
```

## 5. Add `framework/Core/Http/Middleware/StartSession.php`

```php
<?php

namespace Framework\Core\Http\Middleware;

use Closure;
use Framework\Core\Request;

class StartSession
{
    public function handle(Request $request, Closure $next)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        return $next($request);
    }
}
```

## 6. Add `app/Services/AuthService.php`

```php
<?php

namespace App\Services;

use App\Models\User;

class AuthService
{
    protected string $sessionKey = 'auth_user_id';

    public function user(): ?User
    {
        $userId = $_SESSION[$this->sessionKey] ?? null;

        if (!$userId) {
            return null;
        }

        return User::find((int) $userId);
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function attempt(string $email, string $password): bool
    {
        $user = User::findByEmail($email);

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user->password)) {
            return false;
        }

        $_SESSION[$this->sessionKey] = (int) $user->id;

        return true;
    }

    public function logout(): void
    {
        unset($_SESSION[$this->sessionKey]);
    }
}
```

## 7. Replace `app/Models/User.php`

```php
<?php

namespace App\Models;

use Framework\Core\Model;

class User extends Model
{
    protected static string $table = 'users';

    public static function findByEmail(string $email): ?self
    {
        return static::where('email', '=', $email)->first();
    }

    public function agents()
    {
        return $this->hasMany(Agent::class, 'user_id');
    }
}
```

## 8. Add `app/Middleware/AuthMiddleware.php`

```php
<?php

namespace App\Middleware;

use App\Services\AuthService;
use Closure;
use Framework\Core\Http\Response;
use Framework\Core\Request;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (app(AuthService::class)->guest()) {
            return Response::redirect('/login');
        }

        return $next($request);
    }
}
```

## 9. Add `app/Middleware/GuestMiddleware.php`

```php
<?php

namespace App\Middleware;

use App\Services\AuthService;
use Closure;
use Framework\Core\Http\Response;
use Framework\Core\Request;

class GuestMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (app(AuthService::class)->check()) {
            return Response::redirect('/dashboard');
        }

        return $next($request);
    }
}
```

## 10. Add `app/Controllers/AuthController.php`

```php
<?php

namespace App\Controllers;

use App\Services\AuthService;
use Framework\Core\Controller;
use Framework\Core\Http\Response;
use Framework\Core\Request;

class AuthController extends Controller
{
    public function showLogin(): string
    {
        return $this->view('auth.login', [
            'title' => 'Login',
            'error' => $_SESSION['auth_error'] ?? null,
        ]);
    }

    public function login(Request $request, AuthService $auth): Response
    {
        $email = trim((string) $request->input('email'));
        $password = (string) $request->input('password');

        if ($auth->attempt($email, $password)) {
            unset($_SESSION['auth_error']);

            return Response::redirect('/dashboard');
        }

        $_SESSION['auth_error'] = 'Invalid credentials.';

        return Response::redirect('/login');
    }

    public function logout(AuthService $auth): Response
    {
        $auth->logout();

        return Response::redirect('/login');
    }
}
```

## 11. Add `app/Controllers/DashboardController.php`

```php
<?php

namespace App\Controllers;

use App\Services\AuthService;
use Framework\Core\Controller;

class DashboardController extends Controller
{
    public function index(): string
    {
        $user = app(AuthService::class)->user();

        return $this->view('dashboard.index', [
            'title' => 'Dashboard',
            'user' => $user,
        ]);
    }
}
```

## 12. Replace `framework/Core/Container.php`

Нужен один апгрейд, чтобы контроллеры могли принимать зависимости в экшены.

```php
<?php

namespace Framework\Core;

use ReflectionMethod;

class Container
{
    protected array $singletons = [];
    protected array $instances = [];
    protected array $aliases = [];

    public function alias(string $abstract, string $alias): void
    {
        $this->aliases[$alias] = $abstract;
    }

    public function instance(string $abstract, $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function singleton(string $abstract, callable $factory): void
    {
        $this->singletons[$abstract] = $factory;
    }

    public function make(string $abstract)
    {
        if (isset($this->aliases[$abstract])) {
            $abstract = $this->aliases[$abstract];
        }

        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (isset($this->singletons[$abstract])) {
            $this->instances[$abstract] = ($this->singletons[$abstract])($this);

            return $this->instances[$abstract];
        }

        $reflection = new \ReflectionClass($abstract);

        if (!$reflection->isInstantiable()) {
            throw new \Exception("Class {$abstract} not instantiable");
        }

        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return new $abstract;
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if ($type && !$type->isBuiltin()) {
                $dependencies[] = $this->make($type->getName());
            } else {
                $dependencies[] = $parameter->isDefaultValueAvailable()
                    ? $parameter->getDefaultValue()
                    : null;
            }
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    public function call(object $instance, string $method)
    {
        $reflection = new ReflectionMethod($instance, $method);
        $dependencies = [];

        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType();

            if ($type && !$type->isBuiltin()) {
                $dependencies[] = $this->make($type->getName());
            } else {
                $dependencies[] = $parameter->isDefaultValueAvailable()
                    ? $parameter->getDefaultValue()
                    : null;
            }
        }

        return $reflection->invokeArgs($instance, $dependencies);
    }
}
```

## 13. Small update inside `framework/Core/Router.php`

Если ты не хочешь целиком менять `Router` из шага 3, то минимум вот эта строка обязательна в `dispatch()`:

```php
return new Response($this->container->call($controller, $methodName));
```

А не:

```php
return new Response($controller->$methodName());
```

Иначе `Request` и `AuthService` не смогут инжектиться в actions.

## 14. Replace `routes/web.php`

```php
<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\HomeController;
use Framework\Core\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);

Route::middleware('guest')->group(function ($router) {
    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function ($router) {
    $router->get('/dashboard', [DashboardController::class, 'index']);
    $router->post('/logout', [AuthController::class, 'logout']);
});
```

## 15. Add `resources/views/auth/login.php`

```php
<?php /** @var string|null $error */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Login</h1>

    <?php if (!empty($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php unset($_SESSION['auth_error']); ?>
    <?php endif; ?>

    <form method="POST" action="/login">
        <div>
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div>
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit">Sign in</button>
    </form>
</section>
@endsection
```

## 16. Add `resources/views/dashboard/index.php`

```php
<?php /** @var \App\Models\User|null $user */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>Dashboard</h1>
    <p>Welcome, {{ $user?->name ?? 'Agent' }}</p>
    <p>You are now inside the protected application area.</p>

    <form method="POST" action="/logout">
        <button type="submit">Logout</button>
    </form>
</section>
@endsection
```

## 17. Replace `resources/views/layouts/app.php`

```php
<?php /** @var string $content */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Agent Pay System') ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f4f7fb;
            color: #1f2937;
        }

        header, footer {
            background: #111827;
            color: white;
            padding: 16px 24px;
        }

        main {
            max-width: 960px;
            margin: 0 auto;
            padding: 32px 24px;
        }

        form {
            display: grid;
            gap: 12px;
            max-width: 360px;
        }

        input, button {
            padding: 10px 12px;
            font-size: 16px;
        }

        button {
            cursor: pointer;
        }
    </style>
</head>
<body>
<header>
    <strong>Agent Pay System</strong>
</header>

<main>
    @yield('content')
</main>

<footer>
    Laravel DNA Phase
</footer>
</body>
</html>
```

## 18. Update `app/Controllers/HomeController.php`

Можно сделать домашнюю страницу чуть честнее:

```php
<?php

namespace App\Controllers;

use Framework\Core\Controller;

class HomeController extends Controller
{
    public function index(): string
    {
        return $this->view('home', [
            'message' => 'Agent Pay System foundation is ready for Level 3.',
        ]);
    }
}
```

## 19. Update `resources/views/home.php`

```php
<?php /** @var string $message */ ?>
@extends('layouts.app')

@section('content')
<section>
    <h1>{{ $message }}</h1>
    <p><a href="/login">Open login</a></p>
</section>
@endsection
```

## 20. Seed user for first login

Для первого запуска нужен пользователь с `password_hash`.

Пример записи в БД:

```sql
INSERT INTO users (name, email, password, role)
VALUES (
    'Admin',
    'admin@example.com',
    '$2y$10$wH9f0T8A3GQj9nP8F5L3Wu2n7dJY7pM1k9xw7cHkZfXqL8wYwPpUe',
    'admin'
);
```

Пароль для этого примера нужно сгенерировать у себя через `password_hash('secret123', PASSWORD_BCRYPT)`.

## Minimum expected result after apply

После внедрения этого пакета ты получишь:

- `/login` для гостя
- `POST /login` с авторизацией
- `auth` protected `/dashboard`
- `POST /logout`
- session-based guard
- Laravel-like middleware routing

## Что делать после этого

Следующая итерация `LEVEL 3.2`:

1. registration flow
2. validation layer
3. flash messages
4. auth helper/facade
5. dashboard widgets
6. payment domain entities and services
