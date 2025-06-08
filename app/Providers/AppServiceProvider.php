<?php

namespace App\Providers;

use LadyPHP\Core\ServiceProvider;
use LadyPHP\Core\Container;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra os serviços do provider
     *
     * @return void
     */
    public function register()
    {
        // Registra o container como singleton
        $this->app->singleton('app', function ($app) {
            return $app;
        });

        // Registra o container como Container
        $this->app->singleton(Container::class, function ($app) {
            return $app;
        });
    }

    /**
     * Inicializa os serviços do provider
     *
     * @return void
     */
    public function boot()
    {
        // Carrega configurações
        $this->loadConfigurations();
    }

    /**
     * Carrega as configurações da aplicação
     *
     * @return void
     */
    protected function loadConfigurations()
    {
        // Carrega configurações do app.php
        if (file_exists($configPath = __DIR__ . '/../../config/app.php')) {
            $config = require $configPath;
            $this->app->singleton('config', function () use ($config) {
                return $config; // Retorna o array de configuração
            });
        }
    }
} 