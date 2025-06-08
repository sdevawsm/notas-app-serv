<?php

namespace App\Middleware;

use LadyPHP\Http\Middleware\MiddlewareInterface;
use LadyPHP\Http\Request;
use LadyPHP\Http\Response;

class AuthMiddleware implements \LadyPHP\Http\Middleware\MiddlewareInterface {
    public function handle(Request $request, callable $next): Response {
        // Verifica se o usuário está autenticado (exemplo usando session)
        if (!isset($_SESSION['user_id'])) {
            // Se não estiver autenticado, redireciona para login
            return new Response(
                '<h1>Acesso Negado</h1><p>Você precisa estar logado para acessar esta página.</p>',
                401,
                ['Location' => '/login']
            );
        }

        // Se estiver autenticado, continua para o próximo middleware/controller
        return $next($request);
    }
} 