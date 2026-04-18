<?php

namespace Yason\WebsiteTemplate\Core;

use Yason\WebsiteTemplate\Core\Support\ServiceProvider;
use Yason\WebsiteTemplate\Core\Config\ConfigLoader;
use Yason\WebsiteTemplate\Core\Http\Kernel;
use Yason\WebsiteTemplate\Core\Router;
use Yason\WebsiteTemplate\Core\Request;

class Application extends Container
{
    protected static self $instance;

    protected array $providers = [];

    protected string $basePath;

    public function __construct(string $basePath)
    {
        static::$instance = $this;

        $this->basePath = $basePath;

        $this->registerBaseBindings();
        $this->loadEnvironment(); // ✅ ОСТАВИТЬ
        $this->loadConfig();
    }

    public static function getInstance(): self
    {
        return static::$instance;
    }

    /*
    |--------------------------------------------------------------------------
    | Base bindings
    |--------------------------------------------------------------------------
    */

    protected function registerBaseBindings(): void
    {
        // Application
        $this->instance(self::class, $this);
        $this->instance('app', $this);

        // Router
        $this->singleton(Router::class, fn($app) => new Router($app));
        $this->alias(Router::class, 'router'); // ⭐ для Facade

        // Kernel
        $this->singleton(Kernel::class, fn($app) => new Kernel($app));

        // Request
        $this->singleton(Request::class, fn() => new Request());
    }

    /*
    |--------------------------------------------------------------------------
    | ENV
    |--------------------------------------------------------------------------
    */

    protected function loadEnvironment(): void
    {
        Env::load($this->basePath('.env'));
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIG
    |--------------------------------------------------------------------------
    */

    protected function loadConfig(): void
    {
        $config = ConfigLoader::load(
            $this->basePath('config')
        );

        $this->instance('config', $config);
    }

    /*
    |--------------------------------------------------------------------------
    | Providers
    |--------------------------------------------------------------------------
    */

    public function register(ServiceProvider $provider): void
    {
        $provider->register();

        $this->providers[] = $provider;
    }

    public function registerProvider(string $providerClass): void
    {
        $provider = new $providerClass($this);

        $this->register($provider);
    }

    public function registerConfiguredProviders(): void
    {
        $composer = json_decode(
            file_get_contents($this->basePath('composer.json')),
            true
        );

        $providers = $composer['extra']['providers'] ?? [];

        foreach ($providers as $provider) {
            $this->registerProvider($provider);
        }
    }

    public function boot(): void
    {
        foreach ($this->providers as $provider) {
            $provider->boot();
        }
    }

    public function basePath(string $path = ''): string
    {
        return $this->basePath . ($path ? '/' . $path : '');
    }
}