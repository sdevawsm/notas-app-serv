<?php

namespace LadyPHP\Database\Connection;

use PDO;
use PDOException;

interface ConnectionInterface {
    public function connect(): PDO;
    public function getPdo(): PDO;
    public function disconnect(): void;
}

abstract class Connection implements ConnectionInterface
{
    protected ?PDO $pdo = null;
    protected array $config;
    protected bool $connected = false;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function connect(): PDO
    {
        if ($this->isConnected()) {
            return $this->pdo;
        }

        try {
            $this->pdo = new PDO(
                $this->getDsn(),
                $this->config['username'] ?? null,
                $this->config['password'] ?? null,
                $this->getOptions()
            );

            $this->connected = true;
            return $this->pdo;
        } catch (PDOException $e) {
            throw new PDOException("Erro ao conectar ao banco de dados: " . $e->getMessage());
        }
    }

    public function disconnect(): void
    {
        $this->pdo = null;
        $this->connected = false;
    }

    public function beginTransaction(): bool
    {
        return $this->getPdo()->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->getPdo()->commit();
    }

    public function rollback(): bool
    {
        return $this->getPdo()->rollBack();
    }

    public function getPdo(): PDO
    {
        if (!$this->isConnected()) {
            $this->connect();
        }

        return $this->pdo;
    }

    public function isConnected(): bool
    {
        return $this->connected && $this->pdo !== null;
    }

    public function query(string $query, array $bindings = []): \PDOStatement
    {
        $statement = $this->prepare($query);
        $statement->execute($bindings);
        return $statement;
    }

    public function prepare(string $query): \PDOStatement
    {
        return $this->getPdo()->prepare($query);
    }

    /**
     * Retorna o DSN para conexão
     */
    abstract protected function getDsn(): string;

    /**
     * Retorna as opções do PDO
     */
    protected function getOptions(): array
    {
        return [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
    }
} 