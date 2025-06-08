<?php

namespace App\Middleware;

use LadyPHP\Http\Middleware\MiddlewareInterface;
use LadyPHP\Http\Request;
use LadyPHP\Http\Response;

class LogMiddleware implements \LadyPHP\Http\Middleware\MiddlewareInterface {
    /**
     * Processa a requisição através do middleware
     * 
     * @param Request $request A requisição atual
     * @param callable $next O próximo middleware na pilha
     * @return Response
     */
    public function handle(Request $request, callable $next): Response {
        // Log antes da requisição
        $startTime = microtime(true);
        $method = $request->getMethod();
        $uri = $request->getUri();
        
        error_log("Iniciando requisição: $method $uri");

        // Processa a requisição
        $response = $next($request);

        // Log após a requisição
        $duration = microtime(true) - $startTime;
        $status = $response->getStatusCode();
        error_log("Requisição finalizada: $method $uri - Status: $status - Duração: {$duration}s");

        return $response;
    }
} 