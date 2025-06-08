<?php

namespace App\Providers;

use LadyPHP\Core\ServiceProvider;
use LadyPHP\Http\Router;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Registra os serviços do provider
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('router', function ($app) {
            return new Router();
        });

        $this->app->singleton(Router::class, function ($app) {
            return $app->make('router');
        });
    }

    /**
     * Inicializa os serviços do provider
     *
     * @return void
     */
    public function boot()
    {
        $router = $this->app->make('router');

        // Carrega as rotas
        $router->loadRoutes(__DIR__ . '/../../routes/web.php');
        $router->loadRoutes(__DIR__ . '/../../routes/api.php');
    }
} 