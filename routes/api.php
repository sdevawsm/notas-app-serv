<?php

use LadyPHP\Auth\JwtAuth;
use LadyPHP\Http\Middleware\JwtAuthMiddleware;
use LadyPHP\Http\Middleware\CorsMiddleware;

// Configuração do JWT
$jwtAuth = new JwtAuth(
    $_ENV['JWT_SECRET'] ?? 'sua-chave-secreta-aqui',
    [
        'expiration' => 3600, // 1 hora
        'issuer' => 'api',
        'audience' => 'api-clients'
    ]
);

// Middleware de autenticação
$authMiddleware = new JwtAuthMiddleware($jwtAuth, [
    '/api/auth/login',
    '/api/auth/register',
    '/api/public/*'
]);

// Middleware CORS
$corsMiddleware = new CorsMiddleware();

// Grupo de rotas da API com CORS global
$router->group([
    'prefix' => '/api',
    'middleware' => $corsMiddleware
], function($router) use ($authMiddleware, $jwtAuth) {
    
    // Grupo de rotas públicas
    $router->group(['prefix' => '/auth'], function($router) use ($jwtAuth) {
        // POST /api/auth/login - Login com verificação de usuário
        $router->post('/login', 'AuthController@apiLogin');
        
        // POST /api/auth/register - Registro de usuário
        $router->post('/register', 'AuthController@register');
        
        // POST /api/auth/logout - Logout
        $router->post('/logout', 'AuthController@logout');
        
        // POST /api/auth/refresh - Atualiza o token
        $router->post('/refresh', 'AuthController@refresh');
    });
    
    // Grupo de rotas protegidas
    $router->group(['middleware' => $authMiddleware], function($router) {
        // GET /api/users - Lista todos os usuários
        $router->get('/users', 'UserController@index');
        
        // GET /api/user - Obtém o usuário atual
        $router->get('/user', 'AuthController@user');
        
        // POST /api/users - Cria um novo usuário (apenas admin)
        $router->post('/users', 'UserController@store');
    });
});

