<?php

namespace Framework\Core;

use Framework\Core\Config\ConfigLoader;
use Framework\Core\Kernel;
use Framework\Core\View\ViewFactory;
use Framework\Support\Facades\Facade;


class Application extends Container
{
    protected static self $instance;
    protected array $providers = [];
    protected array $booted = [];
    protected string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;

        static::$instance = $this;

        $this->loadEnvironment();

        $this->loadConfig();

        $this->registerBaseBindings();

        Facade::setFacadeApplication($this);

        $this->registerBaseServices();

        $this->registerConfiguredProviders();

        $this->loadRoutes();
    }

    protected function registerBaseServices()
    {
        $this->singleton(Router::class, function ($app) {
            return new Router($app);
        });

        $this->singleton('view', function ($app) {
            return new \Framework\Core\View\ViewFactory(
                $app->basePath('resources/views'),
                $app->basePath('storage/cache/views')
            );
        });
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
        $this->singleton(
            \Framework\Core\Router::class,
            fn($app) => new \Framework\Core\Router($app)
        );

        $this->alias(
            \Framework\Core\Router::class,
            'router'
        );

        // Kernel
        $this->singleton(Kernel::class, fn($app) => new Kernel($app, $app->make(Router::class)));

        // Request
//        $this->singleton(Request::class, fn() => new Request());

        $this->singleton(ViewFactory::class, function ($app) {
            return new ViewFactory(
                $app->basePath('resources/views'),
                $app->basePath('storage/cache/views')
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

    public function register($provider)
    {
        $provider->register();

        $this->providers[] = $provider;
    }

    public function bootProviders(): void
    {
        foreach ($this->providers as $provider) {
            if (method_exists($provider, 'boot')) {
                $provider->boot();
            }
        }
    }

    public function boot()
    {
        $this->bootProviders();
    }

    protected function registerConfiguredProviders(): void
    {
        $composer = json_decode(
            file_get_contents($this->basePath . '/composer.json'),
            true
        );

        foreach (($composer['extra']['providers'] ?? []) as $providerClass) {
            $provider = new $providerClass($this);
            $this->register($provider);
        }
    }

    protected function loadRoutes(): void
    {
        require $this->basePath('routes/web.php');
    }


    public function basePath(string $path = ''): string
    {
        return $this->basePath . ($path ? '/' . $path : '');
    }

    public function bootstrap(): void
    {
        $this->registerBaseBindings();

        $this->registerConfiguredProviders();

        $this->loadRoutes();
    }

}