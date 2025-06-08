<?php

namespace LadyPHP\Database\Migrations;

use PDO;

class SchemaBuilder
{
    protected PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Cria uma nova tabela
     */
    public function create(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);
        $this->execute($blueprint->toSql());
    }

    /**
     * Modifica uma tabela existente
     */
    public function table(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table, true);
        $callback($blueprint);
        $this->execute($blueprint->toSql());
    }

    /**
     * Remove uma tabela
     */
    public function drop(string $table): void
    {
        $this->execute("DROP TABLE IF EXISTS {$table}");
    }

    /**
     * Remove uma tabela se ela existir
     */
    public function dropIfExists(string $table): void
    {
        $this->execute("DROP TABLE IF EXISTS `{$table}`");
    }

    /**
     * Verifica se uma tabela existe
     */
    public function hasTable(string $table): bool
    {
        $sql = "SELECT COUNT(*) FROM information_schema.tables WHERE table_name = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$table]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Verifica se uma coluna existe
     */
    public function hasColumn(string $table, string $column): bool
    {
        $sql = "SELECT COUNT(*) FROM information_schema.columns WHERE table_name = ? AND column_name = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$table, $column]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Executa uma query SQL
     */
    protected function execute(string $sql): void
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
    }
} 