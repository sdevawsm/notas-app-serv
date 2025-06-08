<?php

namespace LadyPHP\Core;

abstract class ServiceProvider {
    /**
     * O container da aplicação
     *
     * @var Container
     */
    protected $app;

    /**
     * Indica se o provider foi carregado
     *
     * @var bool
     */
    protected $loaded = false;

    /**
     * Cria uma nova instância do service provider
     *
     * @param Container $app
     * @return void
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Registra os serviços do provider
     *
     * @return void
     */
    abstract public function register();

    /**
     * Inicializa os serviços do provider
     *
     * @return void
     */
    public function boot()
    {
        // Hook para inicialização após registro
    }

    /**
     * Marca o provider como carregado
     *
     * @return void
     */
    public function markAsLoaded()
    {
        $this->loaded = true;
    }

    /**
     * Verifica se o provider já foi carregado
     *
     * @return bool
     */
    public function isLoaded()
    {
        return $this->loaded;
    }
}
