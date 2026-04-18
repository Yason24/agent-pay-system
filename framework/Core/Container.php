<?php

namespace Framework\Core;

use ReflectionClass;
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

    /*
    |--------------------------------------------------------------------------
    | singleton
    |--------------------------------------------------------------------------
    */

    public function singleton(string $abstract, callable $factory): void
    {
        $this->singletons[$abstract] = $factory;
    }
    /*
    |--------------------------------------------------------------------------
    | MAKE CLASS
    |--------------------------------------------------------------------------
    */
    public function make(string $abstract)
    {
        if (isset($this->aliases[$abstract])) {
            $abstract = $this->aliases[$abstract];
        }

        /*
        |--------------------------------------------------------------------------
        | Existing instance
        |--------------------------------------------------------------------------
        */

        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        /*
        |--------------------------------------------------------------------------
        | Singleton
        |--------------------------------------------------------------------------
        */

        if (isset($this->singletons[$abstract])) {

            $this->instances[$abstract] =
                ($this->singletons[$abstract])($this);

            return $this->instances[$abstract];
        }

        /*
        |--------------------------------------------------------------------------
        | Auto resolve
        |--------------------------------------------------------------------------
        */

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
                $dependencies[] = null;
            }
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    /*
    |--------------------------------------------------------------------------
    | CALL METHOD WITH AUTO DI
    |--------------------------------------------------------------------------
    */
    public function call(object $instance, string $method)
    {
        $reflection = new ReflectionMethod($instance, $method);

        $dependencies = [];

        foreach ($reflection->getParameters() as $parameter) {

            $type = $parameter->getType();

            if ($type && !$type->isBuiltin()) {
                $dependencies[] = $this->make(
                    $type->getName()
                );
            } else {
                $dependencies[] = null;
            }
        }

        return $reflection->invokeArgs(
            $instance,
            $dependencies
        );
    }
}