<?php

// Configurações de CORS
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');

// Se for uma requisição OPTIONS, retorna imediatamente
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Configurações de Debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

// Carrega o bootstrap da aplicação
$app = require_once __DIR__ . '/../bootstrap/bootstrap.php';

// Adiciona o middleware de verificação de migrations
$app->addMiddleware(new LadyPHP\Http\Middleware\CheckMigrationsMiddleware(
    new LadyPHP\Database\MigrationManager($app->make('db'))
));

// Executa a aplicação //
$app->run();