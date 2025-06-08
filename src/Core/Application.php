<?php

namespace LadyPHP\Core;

use LadyPHP\Http\Request;
use LadyPHP\Http\Response;
use LadyPHP\Http\Router;
use LadyPHP\Config\Config;
use LadyPHP\Http\Middleware\MiddlewareInterface;

class Application extends Container {
    /**
     * A versão do framework
     *
     * @var string
     */
    const VERSION = '1.0.0';

    /**
     * A instância singleton da aplicação
     *
     * @var static
     */
    protected static $instance;

    /**
     * Os providers registrados
     *
     * @var array
     */
    protected $providers = [];

    /**
     * Os providers carregados
     *
     * @var array
     */
    protected $loadedProviders = [];

    /**
     * Os middlewares globais da aplicação
     *
     * @var array
     */
    protected $middlewares = [];

    /**
     * Cria uma nova instância da aplicação
     *
     * @return void
     */
    public function __construct() {
        static::$instance = $this;

        $this->registerBaseBindings();
        $this->registerConfiguredProviders();
    }

    /**
     * Registra os bindings base da aplicação
     *
     * @return void
     */
    protected function registerBaseBindings() {
        static::setInstance($this);

        $this->instance('app', $this);
        $this->instance(Container::class, $this);
        
        // Registra o Config no container
        $config = new Config();
        $config->load(__DIR__ . '/../../config/app.php');
        $this->instance('config', $config);
    }

    /**
     * Registra os providers configurados em config/app.php
     *
     * @return void
     */
    protected function registerConfiguredProviders() {
        $config = $this->make('config');
        $providers = $config->get('providers', []);
        foreach ($providers as $provider) {
            $this->register($provider);
        }
    }

    /**
     * Registra um service provider
     *
     * @param string $provider
     * @return void
     */
    public function register($provider) {
        if (isset($this->loadedProviders[$provider])) {
            return;
        }

        $providerInstance = new $provider($this);
        $providerInstance->register();
        $providerInstance->boot();
        $providerInstance->markAsLoaded();

        $this->loadedProviders[$provider] = true;
    }

    /**
     * Registra um array de service providers
     *
     * @param array $providers
     * @return void
     */
    public function registerProviders(array $providers) {
        foreach ($providers as $provider) {
            $this->register($provider);
        }
    }

    /**
     * Retorna a instância da aplicação
     *
     * @return static
     */
    public static function getInstance() {
        return static::$instance;
    }

    /**
     * Define a instância da aplicação
     *
     * @param static $instance
     * @return void
     */
    public static function setInstance($instance) {
        static::$instance = $instance;
    }

    /**
     * Adiciona um middleware global à aplicação
     *
     * @param MiddlewareInterface $middleware
     * @return void
     */
    public function addMiddleware(MiddlewareInterface $middleware): void {
        $this->middlewares[] = $middleware;
    }

    /**
     * Executa a aplicação
     *
     * @return void
     */
    public function run() {
        $request = $this->make(Request::class);
        $response = $this->processMiddleware($request, 0);
        $response->send();
    }

    /**
     * Processa a pilha de middlewares
     *
     * @param Request $request
     * @param int $index
     * @return Response
     */
    protected function processMiddleware(Request $request, int $index): Response {
        if ($index >= count($this->middlewares)) {
            // Se não houver mais middlewares, processa a rota
            return $this->processRoute($request);
        }

        $middleware = $this->middlewares[$index];
        return $middleware->handle($request, function($request) use ($index) {
            return $this->processMiddleware($request, $index + 1);
        });
    }

    /**
     * Processa a rota atual
     *
     * @param Request $request
     * @return Response
     */
    protected function processRoute(Request $request): Response {
        $router = $this->make(Router::class);
        $route = $router->resolve($request);
        return $router->dispatch($route, $request);
    }

    public function getRouter(): Router {
        return $this->make(Router::class);
    }

    public function getConfig(string $key = null) {}
    public function loadConfig(): void {}
    public function handleRequest(): Response {}
    public function handleException(\Exception $e): void {}
}
