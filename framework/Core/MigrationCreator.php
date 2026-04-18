<?php

namespace Yason\WebsiteTemplate\Core;

class MigrationCreator
{
    public function create(string $name): void
    {
        $timestamp = date('YmdHis');

        $filename = ROOT . "/migrations/{$timestamp}_{$name}.php";

        $template = <<<PHP
<?php

return function(PDO \$db) {

    \$db->exec("
        -- write SQL here
    ");

};
PHP;

        file_put_contents($filename, $template);

        echo "Migration created: {$timestamp}_{$name}.php\n";
    }
}