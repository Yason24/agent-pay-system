<?php

namespace Framework\Core;

use Framework\Core\Support\ServiceProvider;
use Framework\Core\Config\ConfigLoader;
use Framework\Core\Http\Kernel;
use Framework\Core\Router;
use Framework\Core\Request;
use Framework\Core\View\ViewFactory;
use Framework\Core\Support\Facades\Facade;


class Application extends Container
{
    protected static self $instance;
    protected array $providers = [];
    protected array $booted = [];
    protected string $basePath;

    public function __construct(string $basePath)
    {
        static::$instance = $this;

        $this->basePath = $basePath;

        /*
        |-----------------------------------
        | Bind Core
        |-----------------------------------
        */
        $this->registerBaseBindings();

        /*
        |-----------------------------------
        | Facade container
        |-----------------------------------
        */
        Facade::setFacadeApplication($this);

        /*
        |-----------------------------------
        | Providers
        |-----------------------------------
        */
        $this->registerConfiguredProviders();
        $this->bootProviders();
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
        $this->singleton(Router::class, function ($app) {
            return new Router($app);
        });

        $this->alias(Router::class, 'router');

        // Kernel
        $this->singleton(Kernel::class, fn($app) => new Kernel($app));

        // Request
        $this->singleton(Request::class, fn() => new Request());

        $this->singleton(ViewFactory::class, function ($app) {
            return new ViewFactory(
                $app->basePath('resources/views')
            );
        });

        $this->singleton(Request::class, fn($app) => Request::capture());
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

    public function register(string $provider): void
    {
        $provider = new $provider($this);

        $provider->register();

        $this->providers[] = $provider;
    }

    public function bootProviders(): void
    {
        foreach ($this->providers as $provider) {
            $provider->boot();
        }
    }

    public function registerProvider(string $providerClass): void
    {
        $provider = new $providerClass($this);

        $this->register($provider);
    }

    protected function registerConfiguredProviders(): void
    {
        $composer = json_decode(
            file_get_contents($this->basePath('composer.json')),
            true
        );

        $providers = $composer['extra']['providers'] ?? [];

        foreach ($providers as $provider) {
            $this->register($provider);
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