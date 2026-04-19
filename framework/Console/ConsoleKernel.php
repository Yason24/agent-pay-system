<?php

namespace Framework\Console;

class ConsoleKernel
{
    protected array $commands = [];

    public function register(string $command): void
    {
        $this->commands[] = new $command();
    }

    public function handle(array $argv): void
    {
        $commandName = $argv[1] ?? null;

        if (!$commandName) {
            echo "No command provided\n";
            return;
        }

        foreach ($this->commands as $command) {

            if ($command->signature() === $commandName) {

                $command->handle($argv);

                return;
            }
        }

        echo "Command not found: {$commandName}\n";
    }
}