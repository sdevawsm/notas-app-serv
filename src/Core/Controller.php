<?php

namespace Core;

use Http\Request;
use Http\Response;

class Controller {
    protected $request;
    protected $response;
    public function __construct() {}
    public function setRequest(Request $request): void {}
    public function setResponse(Response $response): void {}
    public function json(array $data, int $status = 200): Response {}
    public function view(string $template, array $data = []): Response {}
    public function redirect(string $url): Response {}
    public function validate(array $rules): array {}
    public function middleware(string $middleware): void {}
} 