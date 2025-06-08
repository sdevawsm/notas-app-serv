<?php

namespace LadyPHP\Database\Migrations;

use PDO;

abstract class Migration
{
    protected PDO $connection;
    protected SchemaBuilder $schema;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
        $this->schema = new SchemaBuilder($connection);
    }

    /**
     * Executa a migração
     */
    abstract public function up(): void;

    /**
     * Reverte a migração
     */
    abstract public function down(): void;

    /**
     * Executa uma query SQL
     */
    protected function execute(string $sql): void
    {
        $this->connection->exec($sql);
    }

    /**
     * Cria uma tabela
     */
    protected function createTable(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);
        $this->execute($blueprint->toSql());
    }

    /**
     * Modifica uma tabela
     */
    protected function table(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table, true);
        $callback($blueprint);
        $this->execute($blueprint->toSql());
    }

    /**
     * Remove uma tabela
     */
    protected function dropTable(string $table): void
    {
        $this->execute("DROP TABLE IF EXISTS {$table}");
    }

    /**
     * Verifica se uma tabela existe
     */
    protected function hasTable(string $table): bool
    {
        $sql = "SELECT COUNT(*) FROM information_schema.tables WHERE table_name = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$table]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Verifica se uma coluna existe
     */
    protected function hasColumn(string $table, string $column): bool
    {
        $sql = "SELECT COUNT(*) FROM information_schema.columns WHERE table_name = ? AND column_name = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$table, $column]);
        return (bool) $stmt->fetchColumn();
    }
} 