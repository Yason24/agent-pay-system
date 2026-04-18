<?php

namespace Yason\WebsiteTemplate\Core\Http;

class MiddlewareRegistry
{
    protected array $middleware = [];

    protected array $groups = [];

    public function alias(string $name, string $class): void
    {
        $this->middleware[$name] = $class;
    }

    public function group(string $name, array $middleware): void
    {
        $this->groups[$name] = $middleware;
    }

    public function resolve(array $middleware): array
    {
        $resolved = [];

        foreach ($middleware as $name) {

            // group
            if (isset($this->groups[$name])) {
                foreach ($this->groups[$name] as $m) {
                    $resolved[] = $this->middleware[$m] ?? $m;
                }
                continue;
            }

            // alias
            $resolved[] = $this->middleware[$name] ?? $name;
        }

        return $resolved;
    }
}