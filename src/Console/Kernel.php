<?php

namespace LadyPHP\Console;

class Kernel
{
    protected array $commands = [];

    public function registerCommand(Command $command): void
    {
        $this->commands[$command->getName()] = $command;
    }

    public function getCommands(): array
    {
        return $this->commands;
    }

    public function handle(array $argv): int
    {
        if (count($argv) < 2) {
            $this->showHelp();
            return 1;
        }

        $command = $argv[1];
        $arguments = array_slice($argv, 2);

        if (!isset($this->commands[$command])) {
            $this->error("Comando '{$command}' não encontrado.");
            $this->showHelp();
            return 1;
        }

        $commandInstance = $this->commands[$command];
        $this->parseArguments($commandInstance, $arguments);

        return $commandInstance->handle();
    }

    protected function parseArguments(Command $command, array $arguments): void
    {
        $parsedArguments = [];
        $parsedOptions = [];

        foreach ($arguments as $argument) {
            if (str_starts_with($argument, '--')) {
                // É uma opção
                $parts = explode('=', $argument, 2);
                $option = $parts[0];
                $value = $parts[1] ?? true;
                $parsedOptions[$option] = $value;
            } else {
                // É um argumento
                $parsedArguments[] = $argument;
            }
        }

        $command->setArguments($parsedArguments);
        $command->setOptions($parsedOptions);
    }

    protected function showHelp(): void
    {
        $this->line("LadyPHP Framework - Console");
        $this->line("Uso: lady <comando> [opções]");
        $this->line("");
        $this->line("Comandos disponíveis:");

        foreach ($this->commands as $command) {
            $this->line(sprintf(
                "  %-20s %s",
                $command->getName(),
                $command->getDescription()
            ));

            // Mostrar argumentos
            if (!empty($command->getArguments())) {
                $this->line("    Argumentos:");
                foreach ($command->getArguments() as $name => $description) {
                    $this->line(sprintf("      %-20s %s", $name, $description));
                }
            }

            // Mostrar opções
            if (!empty($command->getOptions())) {
                $this->line("    Opções:");
                foreach ($command->getOptions() as $name => $description) {
                    $this->line(sprintf("      %-20s %s", $name, $description));
                }
            }

            $this->line("");
        }
    }

    protected function error(string $message): void
    {
        echo "\033[31m{$message}\033[0m" . PHP_EOL;
    }

    protected function line(string $message): void
    {
        echo $message . PHP_EOL;
    }
} 