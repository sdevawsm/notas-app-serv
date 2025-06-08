<?php

namespace LadyPHP\Console;

abstract class Command
{
    protected string $name;
    protected string $description;
    protected array $arguments = [];
    protected array $options = [];

    abstract public function handle(): int;

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setArguments(array $arguments): void
    {
        $this->arguments = $arguments;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    protected function info(string $message): void
    {
        echo "\033[32m{$message}\033[0m" . PHP_EOL;
    }

    protected function error(string $message): void
    {
        echo "\033[31m{$message}\033[0m" . PHP_EOL;
    }

    protected function warn(string $message): void
    {
        echo "\033[33m{$message}\033[0m" . PHP_EOL;
    }

    protected function line(string $message): void
    {
        echo $message . PHP_EOL;
    }
} 