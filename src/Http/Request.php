<?php

namespace LadyPHP\Http;

class Request {
    protected array $get;
    protected array $post;
    protected array $server;
    protected array $headers;
    protected string $method;
    protected string $uri;
    protected array $files;
    protected ?string $body = null;
    protected array $attributes = [];

    public function __construct() {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->files = $_FILES;
        $this->headers = $this->getRequestHeaders();
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $this->body = file_get_contents('php://input');
    }

    public function getMethod(): string {
        return $this->method;
    }

    public function getUri(): string {
        return $this->uri;
    }

    public function get(string $key, $default = null) {
        return $this->get[$key] ?? $default;
    }

    public function post(string $key, $default = null) {
        return $this->post[$key] ?? $default;
    }

    public function input(string $key, $default = null) {
        return $this->all()[$key] ?? $default;
    }

    public function all(): array {
        $data = array_merge($this->get, $this->post);
        
        if ($this->isJson()) {
            $jsonData = $this->json();
            if (is_array($jsonData)) {
                $data = array_merge($data, $jsonData);
            }
        }
        
        return $data;
    }

    public function has(string $key): bool {
        return isset($this->all()[$key]);
    }

    public function header(string $key, $default = null): string {
        return $this->headers[$key] ?? $default;
    }

    public function file(string $key): ?array {
        return $this->files[$key] ?? null;
    }

    public function validate(array $rules): array {
        // TODO: Implementar validação
        return $this->all();
    }

    /**
     * Obtém o corpo bruto da requisição
     * @return string|null
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * Verifica se a requisição é JSON
     * @return bool
     */
    public function isJson(): bool
    {
        $contentType = $this->header('Content-Type');
        return strpos($contentType, 'application/json') !== false;
    }

    /**
     * Obtém o corpo da requisição como array JSON
     * @return array
     */
    public function json(): array
    {
        if (!$this->isJson()) {
            return [];
        }
        $body = $this->getBody();
        if (empty($body)) {
            return [];
        }
        try {
            return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return [];
        }
    }

    public function setAttribute(string $key, $value): void {
        $this->attributes[$key] = $value;
    }

    public function getAttribute(string $key, $default = null) {
        return $this->attributes[$key] ?? $default;
    }

    public function only(array $keys): array {
        return array_intersect_key($this->all(), array_flip($keys));
    }

    public function except(array $keys): array {
        return array_diff_key($this->all(), array_flip($keys));
    }

    public function filled(string $key): bool {
        $value = $this->input($key);
        return !empty($value);
    }

    public function missing(string $key): bool {
        return !$this->has($key);
    }

    public function boolean(string $key, $default = false): bool {
        $value = $this->input($key, $default);
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function integer(string $key, $default = 0): int {
        return (int) $this->input($key, $default);
    }

    public function float(string $key, $default = 0.0): float {
        return (float) $this->input($key, $default);
    }

    public function string(string $key, $default = ''): string {
        return (string) $this->input($key, $default);
    }

    public function array(string $key, $default = []): array {
        $value = $this->input($key, $default);
        return is_array($value) ? $value : [$value];
    }

    public function date(string $key, $format = 'Y-m-d', $default = null): ?\DateTime {
        $value = $this->input($key, $default);
        if (empty($value)) {
            return null;
        }
        try {
            return \DateTime::createFromFormat($format, $value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Obtém os headers da requisição de forma compatível com todos os servidores
     * @return array
     */
    private function getRequestHeaders(): array
    {
        // Tenta usar getallheaders() primeiro (disponível em Apache)
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        // Implementação alternativa usando $_SERVER
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            // Headers HTTP padrão
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }
            // Headers especiais do servidor
            elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'])) {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower($key))));
                $headers[$header] = $value;
            }
        }

        // Adiciona headers de autenticação se presentes
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $headers['Authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW']);
        }

        return $headers;
    }
}
