<?php

namespace App\Middleware;

use LadyPHP\Http\Middleware\MiddlewareInterface;
use LadyPHP\Http\Request;
use LadyPHP\Http\Response;

class CorsMiddleware implements \LadyPHP\Http\Middleware\MiddlewareInterface {
    private array $allowedOrigins;
    private array $allowedMethods;
    private array $allowedHeaders;

    public function __construct(
        array $allowedOrigins = ['*'],
        array $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        array $allowedHeaders = ['Content-Type', 'Authorization']
    ) {
        $this->allowedOrigins = $allowedOrigins;
        $this->allowedMethods = $allowedMethods;
        $this->allowedHeaders = $allowedHeaders;
    }

    public function handle(Request $request, callable $next): Response {
        $origin = $request->header('Origin');

        // Se for uma requisição OPTIONS (preflight), responde imediatamente
        if ($request->getMethod() === 'OPTIONS') {
            return $this->handlePreflight($origin);
        }

        // Processa a requisição normalmente
        $response = $next($request);

        // Adiciona os headers CORS na resposta
        return $this->addCorsHeaders($response, $origin);
    }

    private function handlePreflight(?string $origin): Response {
        $headers = [
            'Access-Control-Allow-Origin' => $this->getAllowedOrigin($origin),
            'Access-Control-Allow-Methods' => implode(', ', $this->allowedMethods),
            'Access-Control-Allow-Headers' => implode(', ', $this->allowedHeaders),
            'Access-Control-Max-Age' => '86400', // 24 horas
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Expose-Headers' => 'Content-Length, Content-Range'
        ];

        return new Response('', 204, $headers);
    }

    private function addCorsHeaders(Response $response, ?string $origin): Response {
        $headers = $response->getHeaders();
        $headers['Access-Control-Allow-Origin'] = $this->getAllowedOrigin($origin);
        $headers['Access-Control-Allow-Credentials'] = 'true';
        $headers['Access-Control-Expose-Headers'] = 'Content-Length, Content-Range';
        
        return new Response(
            $response->getContent(),
            $response->getStatusCode(),
            $headers
        );
    }

    private function getAllowedOrigin(?string $origin): string {
        if (in_array('*', $this->allowedOrigins)) {
            return '*';
        }
        
        return in_array($origin, $this->allowedOrigins) ? $origin : $this->allowedOrigins[0];
    }
} 