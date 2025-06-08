<?php

namespace LadyPHP\Http\Middleware;

use LadyPHP\Auth\JwtAuth;
use LadyPHP\Http\Request;
use LadyPHP\Http\Response;

class JwtAuthMiddleware
{
    /**
     * @var JwtAuth
     */
    private JwtAuth $auth;

    /**
     * @var array Rotas que não precisam de autenticação
     */
    private array $publicRoutes;

    /**
     * @param JwtAuth $auth Instância do serviço de autenticação
     * @param array $publicRoutes Rotas públicas (ex: ['/login', '/register'])
     */
    public function __construct(JwtAuth $auth, array $publicRoutes = [])
    {
        $this->auth = $auth;
        $this->publicRoutes = $publicRoutes;
    }

    /**
     * Processa a requisição
     * @param Request $request
     * @param callable $next
     * @return Response
     */
    public function process(Request $request, callable $next): Response
    {
        // Verifica se a rota é pública
        if ($this->isPublicRoute($request->getPath())) {
            return $next($request);
        }

        // Extrai o token do header
        $token = $this->extractToken($request);
        if (!$token) {
            return $this->unauthorized('Token não fornecido');
        }

        // Valida o token
        $payload = $this->auth->validate($token);
        if (!$payload) {
            return $this->unauthorized('Token inválido ou expirado');
        }

        // Adiciona o usuário à requisição
        $this->addUserToRequest($request, $payload);

        // Continua o processamento
        return $next($request);
    }

    /**
     * Verifica se a rota é pública
     * @param string $path
     * @return bool
     */
    private function isPublicRoute(string $path): bool
    {
        foreach ($this->publicRoutes as $route) {
            // Suporta wildcards simples (ex: /api/public/*)
            if (str_ends_with($route, '/*')) {
                $baseRoute = rtrim($route, '/*');
                if (str_starts_with($path, $baseRoute)) {
                    return true;
                }
            }
            // Rota exata
            elseif ($path === $route) {
                return true;
            }
        }
        return false;
    }

    /**
     * Extrai o token do header Authorization
     * @param Request $request
     * @return string|null
     */
    private function extractToken(Request $request): ?string
    {
        $authHeader = $request->getHeader('Authorization');
        if (!$authHeader) {
            return null;
        }

        // Verifica se o header está no formato "Bearer {token}"
        if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return null;
        }

        return $matches[1];
    }

    /**
     * Adiciona o usuário à requisição
     * @param Request $request
     * @param array $payload
     */
    private function addUserToRequest(Request $request, array $payload): void
    {
        // Remove claims padrão do JWT
        $userData = array_diff_key($payload, array_flip([
            'iss', 'sub', 'aud', 'exp', 'nbf', 'iat', 'jti'
        ]));

        // Adiciona o usuário à requisição
        $request->setAttribute('user', $userData);
        $request->setAttribute('user_id', $payload['sub'] ?? null);
    }

    /**
     * Retorna uma resposta de não autorizado
     * @param string $message
     * @return Response
     */
    private function unauthorized(string $message): Response
    {
        return new Response(
            json_encode(['error' => $message]),
            401,
            ['Content-Type' => 'application/json']
        );
    }
} 