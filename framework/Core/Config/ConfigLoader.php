<?php

namespace Framework\Core\Config;

class ConfigLoader
{
    public static function load(string $path): ConfigRepository
    {
        $repository = new ConfigRepository();

        foreach (glob($path.'/*.php') as $file) {

            $name = basename($file, '.php');

            $repository->set(
                $name,
                require $file
            );
        }

        return $repository;
    }
}