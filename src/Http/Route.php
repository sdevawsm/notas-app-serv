<?php

namespace LadyPHP\Http;

class Route {
    protected string $method;
    protected string $pattern;
    protected $handler;
    protected array $middleware = [];
    protected array $parameters = [];
    protected string $name;
    public function __construct(string $method, string $pattern, $handler) {
        $this->method = $method;
        $this->pattern = $pattern;
        $this->handler = $handler;
    }
    public function middleware($middleware): self {
        if (is_string($middleware)) {
            $this->middleware[] = $middleware;
        } else {
            $this->middleware[] = $middleware;
        }
        return $this;
    }
    public function name(string $name): self {}
    public function matches(string $method, string $uri): bool {}
    public function getHandler() {
        return $this->handler;
    }
    public function getMiddleware(): array {
        return $this->middleware;
    }
    public function getParameters(): array {}
    public function setParameters(array $params): self {}
    public function getName(): string {}
    public function getPattern(): string {
        return $this->pattern;
    }
}
