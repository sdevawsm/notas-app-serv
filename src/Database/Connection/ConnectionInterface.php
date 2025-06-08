<?php

namespace LadyPHP\Database\Connection;

use PDO;

interface ConnectionInterface
{
    /**
     * Estabelece a conexão com o banco de dados
     */
    public function connect(): PDO;

    /**
     * Fecha a conexão com o banco de dados
     */
    public function disconnect(): void;

    /**
     * Inicia uma transação
     */
    public function beginTransaction(): bool;

    /**
     * Confirma uma transação
     */
    public function commit(): bool;

    /**
     * Reverte uma transação
     */
    public function rollback(): bool;

    /**
     * Retorna a instância PDO
     */
    public function getPdo(): PDO;

    /**
     * Verifica se está conectado
     */
    public function isConnected(): bool;

    /**
     * Executa uma query
     */
    public function query(string $query, array $bindings = []): \PDOStatement;

    /**
     * Prepara uma query
     */
    public function prepare(string $query): \PDOStatement;
} 