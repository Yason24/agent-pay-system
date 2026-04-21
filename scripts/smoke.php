<?php

declare(strict_types=1);

use App\Models\User;
use Framework\Core\Application;
use Framework\Core\Database;
use Framework\Core\Env;
use Framework\Core\QueryBuilder;

$basePath = dirname(__DIR__);
define('BASE_PATH', $basePath);

require BASE_PATH . '/vendor/autoload.php';

$failures = [];

$assert = static function (bool $condition, string $message) use (&$failures): void {
    if ($condition) {
        echo "[PASS] {$message}\n";
        return;
    }

    echo "[FAIL] {$message}\n";
    $failures[] = $message;
};

try {
    Env::load(BASE_PATH . '/.env');
    $assert(true, 'Env loaded');
} catch (Throwable $e) {
    $assert(false, 'Env load failed: ' . $e->getMessage());
}

$requiredEnv = ['DB_DRIVER', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME'];
foreach ($requiredEnv as $key) {
    $value = Env::get($key);
    $assert($value !== null && $value !== '', "Env {$key} is set");
}

$availableDrivers = PDO::getAvailableDrivers();
$assert(in_array('pgsql', $availableDrivers, true), 'PDO pgsql driver is available');

try {
    $pdo = Database::getConnection();
    $assert($pdo instanceof PDO, 'Database::getConnection returns PDO');

    $row = $pdo->query('SELECT 1 AS ok')->fetch();
    $assert((string)($row['ok'] ?? '') === '1', 'Database query SELECT 1 works');
} catch (Throwable $e) {
    $assert(false, 'Database connection/check failed: ' . $e->getMessage());
}

try {
    /** @var Application $app */
    $app = require BASE_PATH . '/bootstrap/app.php';
    $assert($app instanceof Application, 'Application bootstrapped');

    $router = $app->make('router');
    $assert($router !== null, 'Router resolved from container');

    $loginRoute = $router->match('GET', '/login');
    $assert(isset($loginRoute['action']), 'GET /login route is registered');

    $registerRoute = $router->match('GET', '/register');
    $assert(isset($registerRoute['action']), 'GET /register route is registered');

    $forgotPasswordRoute = $router->match('GET', '/forgot-password');
    $assert(isset($forgotPasswordRoute['action']), 'GET /forgot-password route is registered');

    $dashboardRoute = $router->match('GET', '/dashboard');
    $assert(isset($dashboardRoute['action']), 'GET /dashboard route is registered');

    $agentsRoute = $router->match('GET', '/agents');
    $assert(isset($agentsRoute['action']), 'GET /agents route is registered');

    $createAgentRoute = $router->match('GET', '/agents/create');
    $assert(isset($createAgentRoute['action']), 'GET /agents/create route is registered');

    $storeAgentRoute = $router->match('POST', '/agents');
    $assert(isset($storeAgentRoute['action']), 'POST /agents route is registered');

    $paymentsRoute = $router->match('GET', '/payments');
    $assert(isset($paymentsRoute['action']), 'GET /payments route is registered');

    $createPaymentRoute = $router->match('GET', '/payments/create');
    $assert(isset($createPaymentRoute['action']), 'GET /payments/create route is registered');

    $storePaymentRoute = $router->match('POST', '/payments');
    $assert(isset($storePaymentRoute['action']), 'POST /payments route is registered');

    $adminUsersRoute = $router->match('GET', '/admin/users');
    $assert(isset($adminUsersRoute['action']), 'GET /admin/users route is registered');

    $createAdminUserRoute = $router->match('GET', '/admin/users/create');
    $assert(isset($createAdminUserRoute['action']), 'GET /admin/users/create route is registered');

    $storeAdminUserRoute = $router->match('POST', '/admin/users');
    $assert(isset($storeAdminUserRoute['action']), 'POST /admin/users route is registered');

    $assert(method_exists(QueryBuilder::class, 'count'), 'QueryBuilder::count method exists');

    $adminsCount = User::where('role', '=', 'admin')->count();
    $assert(is_int($adminsCount), 'User::where(...)->count() returns int');
} catch (Throwable $e) {
    $assert(false, 'Application/router check failed: ' . $e->getMessage());
}

if ($failures !== []) {
    echo "\nSmoke result: FAILED\n";
    exit(1);
}

echo "\nSmoke result: PASSED\n";
exit(0);

