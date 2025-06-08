<?php

namespace LadyPHP\Http\Middleware;

use LadyPHP\Http\Request;
use LadyPHP\Http\Response;

class CorsMiddleware implements MiddlewareInterface {
    public function handle(Request $request, callable $next): Response {
        // Se for uma requisição OPTIONS, retorna imediatamente
        if ($request->getMethod() === 'OPTIONS') {
            $response = new Response('', 204);
        } else {
            $response = $next($request);
        }

        // Define os cabeçalhos CORS
        $response->setHeader('Access-Control-Allow-Origin', 'http://localhost:5173');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN');
        $response->setHeader('Access-Control-Allow-Credentials', 'true');
        $response->setHeader('Access-Control-Max-Age', '86400');

        return $response;
    }
}
