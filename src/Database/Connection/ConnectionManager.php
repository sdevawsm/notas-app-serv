<?php

namespace LadyPHP\Database\Connection;

use LadyPHP\Database\Config\DatabaseConfig;
use LadyPHP\Database\Connection\Drivers\MySQLConnection;
use PDO;

class ConnectionManager
{
    private static ?self $instance = null;
    private array $connections = [];
    private string $defaultConnection;

    private function __construct()
    {
        $this->initializeConnections();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initializeConnections(): void
    {
        $config = [
            'driver' => 'mysql',
            'host' => DatabaseConfig::get('DB_HOST', 'localhost'),
            'port' => DatabaseConfig::get('DB_PORT', '3306'),
            'database' => DatabaseConfig::get('DB_DATABASE'),
            'username' => DatabaseConfig::get('DB_USERNAME', 'root'),
            'password' => DatabaseConfig::get('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ];

        $this->defaultConnection = 'mysql';
        $this->addConnection('mysql', $config);
    }

    public function addConnection(string $name, array $config): void
    {
        switch ($config['driver']) {
            case 'mysql':
                $this->connections[$name] = new MySQLConnection($config);
                break;
            default:
                throw new \Exception("Driver {$config['driver']} não suportado");
        }
    }

    /**
     * Define a conexão padrão
     */
    public function setDefaultConnection(string $name): void
    {
        if (!isset($this->connections[$name])) {
            throw new \Exception("Conexão '$name' não encontrada");
        }
        $this->defaultConnection = $name;
    }

    /**
     * Retorna uma conexão específica
     */
    public function getConnection(string $name = null): PDO
    {
        $name = $name ?? $this->defaultConnection;
        
        if (!isset($this->connections[$name])) {
            throw new \Exception("Conexão '$name' não encontrada");
        }
        
        return $this->connections[$name]->getPdo();
    }

    /**
     * Retorna a instância PDO da conexão
     */
    public function getPdo(string $name = null): PDO
    {
        return $this->getConnection($name);
    }

    /**
     * Executa uma transação
     */
    public function transaction(callable $callback, string $connection = null)
    {
        $connection = $this->getConnection($connection);
        
        try {
            $connection->beginTransaction();
            
            $result = $callback($connection);
            
            $connection->commit();
            
            return $result;
        } catch (\Throwable $e) {
            $connection->rollback();
            throw $e;
        }
    }

    /**
     * Fecha todas as conexões
     */
    public function disconnectAll(): void
    {
        foreach ($this->connections as $connection) {
            $connection->disconnect();
        }
    }

    /**
     * Verifica se uma conexão existe
     */
    public function hasConnection(string $name): bool
    {
        return isset($this->connections[$name]);
    }

    /**
     * Retorna todas as conexões
     */
    public function getConnections(): array
    {
        return $this->connections;
    }
} 