<?php

namespace LadyPHP\Http;

class Response {
    protected int $statusCode;
    protected array $headers;
    protected string $content;
    protected string $contentType;
    public function __construct(string $content = '', int $status = 200, array $headers = []) {
        $this->content = $content;
        $this->statusCode = $status;
        $this->headers = array_merge([
            'Content-Type' => 'text/html; charset=UTF-8'
        ], $headers);
    }
    public function setStatusCode(int $code): self {
        $this->statusCode = $code;
        return $this;
    }
    public function setHeader(string $name, string $value): self {
        $this->headers[$name] = $value;
        return $this;
    }
    public function setContent(string $content): self {
        $this->content = $content;
        return $this;
    }
    public function json(array $data, int $status = 200): self {}
    public function redirect(string $url, int $status = 302): self {}
    public function view(string $template, array $data = []): self {}
    public function send(): void {
        // Envia o status code
        http_response_code($this->statusCode);

        // Envia os headers
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        // Envia o conteúdo
        echo $this->content;
    }
    public function getStatusCode(): int {
        return $this->statusCode;
    }
    public function getHeaders(): array {
        return $this->headers;
    }
    public function getContent(): string {
        return $this->content;
    }
}
