<?php

namespace App\Console\Commands;

class MakeController
{
    public static function signature(): string
    {
        return 'make:controller';
    }

    public function handle()
    {
        global $argv;

        $name = $argv[2] ?? null;

        if (!$name) {
            echo "Controller name required\n";
            return;
        }

        $stub = <<<PHP
<?php

namespace App\Controllers;

class {$name}
{
    public function index()
    {
        return "Hello from {$name}";
    }
}
PHP;

        file_put_contents(
            BASE_PATH."/app/Controllers/{$name}.php",
            $stub
        );

        echo "Controller created: {$name}\n";
    }
}