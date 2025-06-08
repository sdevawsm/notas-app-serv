<?php

namespace LadyPHP\Database\Connection\Drivers;

use PDO;
use LadyPHP\Database\Connection\AbstractConnection;

class MySQLConnection extends AbstractConnection
{
    protected ?PDO $pdo = null;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function connect(): PDO
    {
        if ($this->pdo === null) {
            $dsn = $this->getDsn();

            $this->pdo = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }

        return $this->pdo;
    }

    public function getPdo(): PDO
    {
        return $this->connect();
    }

    public function disconnect(): void
    {
        $this->pdo = null;
    }

    protected function getDsn(): string
    {
        return sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $this->config['host'],
            $this->config['port'],
            $this->config['database'],
            $this->config['charset']
        );
    }

    protected function getOptions(): array
    {
        $options = parent::getOptions();

        // Adiciona opções específicas do MySQL
        $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci";
        
        if (isset($this->config['strict']) && $this->config['strict']) {
            $options[PDO::MYSQL_ATTR_INIT_COMMAND] .= ", SET SESSION sql_mode='STRICT_ALL_TABLES'";
        }

        return $options;
    }
} 