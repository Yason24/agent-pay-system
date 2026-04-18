<?php

namespace Yason\WebsiteTemplate\Core;

use ReflectionClass;
use ReflectionMethod;

class Container
{
    protected array $instances = [];

    /*
    |--------------------------------------------------------------------------
    | MAKE CLASS
    |--------------------------------------------------------------------------
    */
    public function make(string $abstract)
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $reflection = new ReflectionClass($abstract);

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
                $dependencies[] = $this->make(
                    $type->getName()
                );
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