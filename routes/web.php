<?php

use App\Middleware\LogMiddleware;
use App\Controllers\UserController;

/**
 * Rotas Web
 * Estas rotas são para a interface web da aplicação
 * Não devem ter o prefixo /api
 */

// Rotas públicas
$router->get('/', 'HomeController@index');
$router->get('/home', 'HomeController@index')
    ->middleware('LogMiddleware')
    ->middleware('TesteMiddleware');

// Rotas de autenticação web
$router->group(['prefix' => '/auth'], function($router) {
    $router->get('/login', 'AuthController@showLoginForm');
    $router->post('/login', 'AuthController@login');
    $router->post('/logout', 'AuthController@logout');
    $router->get('/register', 'AuthController@showRegisterForm');
    $router->post('/register', 'AuthController@register');
});

// Rotas administrativas (protegidas)
$router->group(['middleware' => ['LogMiddleware', 'AuthMiddleware'], 'prefix' => '/admin'], function($router) {
    $router->get('/dashboard', 'AdminController@dashboard');
    $router->get('/users', 'AdminController@users');
    $router->get('/users/create', 'AdminController@createUser');
    $router->post('/users', 'AdminController@storeUser');
    $router->get('/users/{id}', 'AdminController@showUser');
    $router->get('/users/{id}/edit', 'AdminController@editUser');
    $router->put('/users/{id}', 'AdminController@updateUser');
    $router->delete('/users/{id}', 'AdminController@deleteUser');
});

// Rotas de usuários
$router->get('/users', 'UserController@index');
$router->get('/users/create', [UserController::class, 'create']);
$router->post('/users', [UserController::class, 'store']);
$router->get('/users/{id}/edit', [UserController::class, 'edit']);
$router->put('/users/{id}', [UserController::class, 'update']);
$router->delete('/users/{id}', [UserController::class, 'delete']);

// Rota para adicionar usuário de teste
$router->get('/adduser', function() {
    $controller = new UserController();
    $result = $controller->addUser();
    
    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
}); 

