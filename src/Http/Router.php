<?php

namespace LadyPHP\Http;

use LadyPHP\Http\Request;
use LadyPHP\Http\Response;
use LadyPHP\Http\Route;

class Router {
    protected array $routes = [];
    protected array $middlewareGroups = [];
    protected string $currentPrefix = '';
    protected array $currentMiddleware = [];
    public function __construct() {}

    /**
     * Registra uma rota GET
     */
    public function get(string $pattern, $handler): Route {
        return $this->addRoute('GET', $pattern, $handler);
    }

    /**
     * Registra uma rota POST
     */
    public function post(string $pattern, $handler): Route {
        return $this->addRoute('POST', $pattern, $handler);
    }

    /**
     * Registra uma rota PUT
     */
    public function put(string $pattern, $handler): Route {
        return $this->addRoute('PUT', $pattern, $handler);
    }

    /**
     * Registra uma rota DELETE
     */
    public function delete(string $pattern, $handler): Route {
        return $this->addRoute('DELETE', $pattern, $handler);
    }

    /**
     * Registra uma rota PATCH
     */
    public function patch(string $pattern, $handler): Route {
        return $this->addRoute('PATCH', $pattern, $handler);
    }

    /**
     * Adiciona uma rota ao array interno
     */
    public function addRoute(string $method, string $pattern, $handler): Route {
        // Aplica prefixo se houver
        $fullPattern = $this->currentPrefix . $pattern;
        $route = new Route($method, $fullPattern, $handler);
        $this->routes[$method][] = $route;
        return $route;
    }

    /**
     * Adiciona um middleware ao grupo atual
     * Pode receber uma string (nome do middleware) ou uma instância de MiddlewareInterface
     */
    public function middleware($middleware): self {
        if (is_string($middleware)) {
            // Se for uma string, assume que é o nome de um middleware registrado
            if (isset($this->middlewareGroups[$middleware])) {
                $this->currentMiddleware = array_merge(
                    $this->currentMiddleware,
                    (array) $this->middlewareGroups[$middleware]
                );
            }
        } else {
            // Se for uma instância, adiciona diretamente
            $this->currentMiddleware[] = $middleware;
        }
        return $this;
    }

    /**
     * Define um grupo de rotas com atributos comuns (prefixo, middleware, etc)
     */
    public function group(array $attributes, callable $callback): void {
        // Salva o estado atual
        $previousPrefix = $this->currentPrefix;
        $previousMiddleware = $this->currentMiddleware;

        // Aplica os atributos do grupo
        if (isset($attributes['prefix'])) {
            $this->currentPrefix .= $attributes['prefix'];
        }
        if (isset($attributes['middleware'])) {
            $this->middleware($attributes['middleware']);
        }

        // Executa o callback para registrar as rotas
        $callback($this);

        // Restaura o estado anterior
        $this->currentPrefix = $previousPrefix;
        $this->currentMiddleware = $previousMiddleware;
    }

    /**
     * Define um prefixo para as próximas rotas registradas
     */
    public function prefix(string $prefix): self {
        $this->currentPrefix = $prefix;
        return $this;
    }

    /**
     * Registra um grupo de middlewares para uso posterior
     */
    public function middlewareGroup(string $name, array $middlewares): void {
        $this->middlewareGroups[$name] = $middlewares;
    }

    /**
     * Processa a requisição através da pilha de middlewares
     */
    protected function processMiddlewareStack(Request $request, array $middlewares, callable $handler): Response {
        // Se não houver middlewares, executa o handler diretamente
        if (empty($middlewares)) {
            return $handler($request);
        }

        // Pega o próximo middleware da pilha
        $middleware = array_shift($middlewares);

        // Debug: mostra informações sobre o middleware
        error_log("Processando middleware: " . (is_string($middleware) ? $middleware : get_class($middleware)));

        // Se for uma string, tenta resolver o middleware
        if (is_string($middleware)) {
            // Remove a classe se for passado o nome completo
            $middleware = str_replace('App\\Middleware\\', '', $middleware);
            $middlewareClass = "App\\Middleware\\{$middleware}";
            
            error_log("Tentando carregar middleware: {$middlewareClass}");
            
            if (!class_exists($middlewareClass)) {
                throw new \RuntimeException("Middleware {$middlewareClass} não encontrado");
            }
            
            $middleware = new $middlewareClass();
            error_log("Middleware instanciado: " . get_class($middleware));
        }

        // Verifica se o middleware implementa a interface correta
        $interfaces = class_implements($middleware);
        $interfaceName = 'LadyPHP\\Http\\Middleware\\MiddlewareInterface';
        
        if (!in_array($interfaceName, $interfaces)) {
            $interfacesStr = implode(', ', $interfaces ?: ['nenhuma']);
            throw new \RuntimeException(
                "Middleware deve implementar {$interfaceName}. " . 
                "Classe recebida: " . get_class($middleware) . 
                " Interfaces implementadas: " . $interfacesStr
            );
        }

        // Cria o próximo callable na pilha
        $next = function (Request $request) use ($middlewares, $handler) {
            return $this->processMiddlewareStack($request, $middlewares, $handler);
        };

        // Executa o middleware atual
        return $middleware->handle($request, $next);
    }

    /**
     * Modifica o método dispatch para processar os middlewares
     */
    public function dispatch(Route $route, Request $request): Response {
        $handler = $route->getHandler();
        $middlewares = array_merge($this->currentMiddleware, $route->getMiddleware());

        // Cria o handler final
        $finalHandler = function (Request $request) use ($handler) {
            // Handler do tipo 'Controller@method'
            if (is_string($handler) && strpos($handler, '@') !== false) {
                list($controllerName, $method) = explode('@', $handler);
                $fqcn = 'App\\Controllers\\' . $controllerName;
                if (class_exists($fqcn)) {
                    $controller = new $fqcn();
                    if (method_exists($controller, $method)) {
                        return $controller->$method($request);
                    }
                    return new Response('Método do controller não encontrado', 500);
                }
                return new Response('Controller não encontrado', 500);
            }
            // Handler é um callable (ex: closure)
            if (is_callable($handler)) {
                return $handler($request);
            }
            return new Response('Handler não implementado', 500);
        };

        // Processa a requisição através da pilha de middlewares
        return $this->processMiddlewareStack($request, $middlewares, $finalHandler);
    }

    /**
     * Resolve a rota baseada no método e URI da requisição
     * Retorna a Route correspondente ou uma rota de fallback (404)
     */
    public function resolve(Request $request): Route {
        $method = strtoupper($request->getMethod());
        $uri = $request->getUri();

        /*echo "<pre>";
        print_r($this->routes);
        echo "</pre>";
        die();*/
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $route) {
                // Verifica correspondência exata por enquanto (pode ser expandido para parâmetros dinâmicos)
                if ($route->getPattern() === $uri) {
                    return $route;
                }
            }
        }
        // Rota não encontrada: retorna uma rota que responde 404
        return new Route($method, $uri, function() {
            return new Response('404 Not Found', 404);
        });
    }

    /**
     * Carrega um arquivo de rotas e registra as rotas no objeto Router
     * O arquivo de rotas deve receber $router como variável global
     */
    public function loadRoutes(string $file): void {
        $router = $this;
        if (file_exists($file)) {
            require $file;
        }
    }

    public function url(string $name, array $params = []): string {}
}
