<?php

namespace LadyPHP\Http;

class Session {
    protected bool $started = false;
    protected string $sessionId;
    public function __construct() {}
    public function start(): void {}
    public function get(string $key, $default = null) {}
    public function set(string $key, $value): void {}
    public function has(string $key): bool {}
    public function remove(string $key): void {}
    public function clear(): void {}
    public function destroy(): void {}
    public function flash(string $key, $value): void {}
    public function getFlash(string $key, $default = null) {}
    public function regenerate(): void {}
    public function getId(): string {}
    public function all(): array {}
}
