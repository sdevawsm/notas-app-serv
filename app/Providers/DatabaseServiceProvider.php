<?php

namespace App\Providers;

use LadyPHP\Core\ServiceProvider;
use LadyPHP\Database\Connection\ConnectionManager;
use PDO;

class DatabaseServiceProvider extends ServiceProvider {
    public function register() {
        // Registra o ConnectionManager como singleton
        $this->app->singleton('db.manager', function ($app) {
            return ConnectionManager::getInstance();
        });

        // Registra a conexão PDO como 'db'
        $this->app->singleton('db', function ($app) {
            $manager = $app->make('db.manager');
            return $manager->getPdo();
        });

        // Registra a classe PDO
        $this->app->singleton(PDO::class, function ($app) {
            return $app->make('db');
        });
    }

    public function boot() {
        // Nada a fazer no boot
    }
} 