<?php

namespace LadyPHP\Database\ORM\Traits;

use LadyPHP\Database\Connection\ConnectionManager;
use LadyPHP\Database\Connection\ConnectionInterface;

trait HasConnection
{
    protected static ?string $connectionName = null;

    /**
     * Define o nome da conexão para o modelo
     */
    public static function setConnectionName(string $name): void
    {
        static::$connectionName = $name;
    }

    /**
     * Retorna o nome da conexão do modelo
     */
    public static function getConnectionName(): ?string
    {
        return static::$connectionName;
    }

    /**
     * Retorna a conexão do modelo
     */
    public static function getConnection(): ConnectionInterface
    {
        return ConnectionManager::getInstance()->connection(static::$connectionName);
    }

    /**
     * Retorna a instância PDO da conexão
     */
    public static function getPdo(): \PDO
    {
        return static::getConnection()->getPdo();
    }

    /**
     * Executa uma transação
     */
    public static function transaction(callable $callback)
    {
        return ConnectionManager::getInstance()->transaction($callback, static::$connectionName);
    }
} 