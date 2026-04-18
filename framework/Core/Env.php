<?php

namespace Yason\WebsiteTemplate\Core;

class Env
{
    private static array $data = [];

    public static function load(string $path): void
    {
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            self::$data[$key] = $value;
        }
    }

    public static function get(string $key): ?string
    {
        return self::$data[$key] ?? null;
    }
}