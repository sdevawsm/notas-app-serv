<?php

namespace LadyPHP\Config;

class Config {
    /**
     * As configurações carregadas
     *
     * @var array
     */
    protected $config = [];

    /**
     * Carrega as configurações do arquivo
     *
     * @param string $path
     * @return void
     */
    public function load($path) {
        if (file_exists($path)) {
            $this->config = require $path;
        }
    }

    /**
     * Obtém uma configuração
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null) {
        return $this->config[$key] ?? $default;
    }

    /**
     * Define uma configuração
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value) {
        $this->config[$key] = $value;
    }
} 