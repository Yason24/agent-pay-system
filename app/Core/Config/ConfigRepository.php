<?php

namespace Yason\WebsiteTemplate\Core\Config;

class ConfigRepository
{
    protected array $items = [];

    public function set(string $key, $value): void
    {
        $this->items[$key] = $value;
    }

    public function get(string $key, $default = null)
    {
        $segments = explode('.', $key);

        $config = $this->items;

        foreach ($segments as $segment) {
            if (!isset($config[$segment])) {
                return $default;
            }

            $config = $config[$segment];
        }

        return $config;
    }

    public function all(): array
    {
        return $this->items;
    }
}