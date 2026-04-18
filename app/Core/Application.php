<?php

namespace Yason\WebsiteTemplate\Core;

use Yason\WebsiteTemplate\Core\Support\ServiceProvider;
use Yason\WebsiteTemplate\Core\Config\ConfigLoader;

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
        $this->loadEnvironment();
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
        $this->instance(Application::class, $this);
        $this->instance('app', $this);
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