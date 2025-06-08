<?php

namespace App\Middleware;

use LadyPHP\Http\Middleware\MiddlewareInterface;
use LadyPHP\Http\Request;
use LadyPHP\Http\Response;

class TesteMiddleware implements \LadyPHP\Http\Middleware\MiddlewareInterface {
    
    private bool $logado;
    
    public function __construct(bool $logado = false) {
        $this->logado = $logado;
    }
    
    public function handle(Request $request, callable $next): Response {
        // Log para debug
        error_log("TesteMiddleware: Verificando autenticação. Logado: " . ($this->logado ? 'Sim' : 'Não'));
        
        // Verifica se o usuário está autenticado
        if (!$this->logado) {
            error_log("TesteMiddleware: Acesso negado - Usuário não está logado");
            
            // Se não estiver autenticado, redireciona para login
            return new Response(
                '<h1>Acesso Negado</h1>' .
                '<p>Você precisa estar logado para acessar esta página.</p>' .
                '<p>Este é um middleware de teste.</p>',
                401,
                ['Location' => '/login']
            );
        }

        error_log("TesteMiddleware: Acesso permitido - Usuário está logado");
        
        // Se estiver autenticado, continua para o próximo middleware/controller
        $response = $next($request);
        
        // Adiciona um header para indicar que passou pelo middleware
        $headers = $response->getHeaders();
        $headers['X-Teste-Middleware'] = 'Processado';
        
        return new Response(
            $response->getContent(),
            $response->getStatusCode(),
            $headers
        );
    }
} 