<?php

namespace LadyPHP\Database;

use PDO;

class MigrationManager {
    private PDO $pdo;
    private string $migrationsPath;
    private string $migrationsTable = 'migrations';

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->migrationsPath = dirname(__DIR__, 2) . '/database/migrations';
    }

    public function hasDatabase(): bool {
        try {
            $this->pdo->query('SELECT 1');
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function hasPendingMigrations(): bool {
        $executedMigrations = $this->getExecutedMigrations();
        $allMigrations = $this->getAllMigrations();
        
        return !empty(array_diff($allMigrations, $executedMigrations));
    }

    public function getPendingMigrations(): array {
        $executedMigrations = $this->getExecutedMigrations();
        $allMigrations = $this->getAllMigrations();
        
        return array_diff($allMigrations, $executedMigrations);
    }

    private function getExecutedMigrations(): array {
        if (!$this->hasTable($this->migrationsTable)) {
            return [];
        }

        $stmt = $this->pdo->query("SELECT migration FROM {$this->migrationsTable}");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function getAllMigrations(): array {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }

        $files = glob($this->migrationsPath . '/*.php');
        return array_map(function($file) {
            return basename($file, '.php');
        }, $files);
    }

    public function runMigrations(): void {
        $pendingMigrations = $this->getPendingMigrations();
        
        if (empty($pendingMigrations)) {
            return;
        }

        $batch = $this->getNextBatchNumber();

        foreach ($pendingMigrations as $migration) {
            try {
                // Inicia a transação
                if (!$this->pdo->inTransaction()) {
                    $this->pdo->beginTransaction();
                }

                $this->runMigration($migration, $batch);
                
                // Confirma a transação
                if ($this->pdo->inTransaction()) {
                    $this->pdo->commit();
                }
            } catch (\Throwable $e) {
                // Se houver erro, reverte a transação
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                throw $e;
            }
        }
    }

    private function getNextBatchNumber(): int {
        if (!$this->hasTable($this->migrationsTable)) {
            return 1;
        }

        $stmt = $this->pdo->query("SELECT MAX(batch) FROM {$this->migrationsTable}");
        return (int) $stmt->fetchColumn() + 1;
    }

    private function runMigration(string $migration, int $batch): void {
        $file = $this->migrationsPath . '/' . $migration . '.php';
        
        if (!file_exists($file)) {
            throw new \Exception("Arquivo de migration não encontrado: {$file}");
        }

        // Verifica se a migração já foi executada
        if ($this->hasTable($this->migrationsTable)) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM {$this->migrationsTable} WHERE migration = ?");
            $stmt->execute([$migration]);
            if ($stmt->fetchColumn() > 0) {
                return; // Migração já foi executada
            }
        }

        require_once $file;

        $className = $this->getMigrationClassName($migration);
        
        if (!class_exists($className)) {
            throw new \Exception("Classe de migration não encontrada: {$className}");
        }

        $instance = new $className($this->pdo);

        // Executa a migração
        $instance->up();
        
        // Registra a migração na tabela migrations
        if ($this->hasTable($this->migrationsTable)) {
            $stmt = $this->pdo->prepare(
                "INSERT INTO {$this->migrationsTable} (migration, batch) VALUES (?, ?)"
            );
            $stmt->execute([$migration, $batch]);
        }
    }

    private function getMigrationClassName(string $migration): string {
        // Remove a data do início do nome do arquivo (formato: YYYY_MM_DD_HHMMSS_)
        $name = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $migration);
        
        // Converte para PascalCase
        $name = str_replace('_', ' ', $name);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);
        
        return $name;
    }

    public function rollback(int $steps = 1): void {
        if ($steps < 1) {
            return;
        }
        if (!$this->hasTable($this->migrationsTable)) {
            return;
        }

        // Busca as migrações executadas mais recentemente
        $stmt = $this->pdo->query(
            "SELECT migration FROM {$this->migrationsTable} 
            ORDER BY batch DESC, id DESC 
            LIMIT {$steps}"
        );
        
        $migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($migrations)) {
            return;
        }

        try {
            // Inicia a transação
            if (!$this->pdo->inTransaction()) {
                $this->pdo->beginTransaction();
            }

            foreach ($migrations as $migration) {
                $this->rollbackMigration($migration);
            }

            // Confirma a transação
            if ($this->pdo->inTransaction()) {
                $this->pdo->commit();
            }
        } catch (\Throwable $e) {
            // Se houver erro, reverte a transação
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    private function rollbackMigration(string $migration): void {
        $file = $this->migrationsPath . '/' . $migration . '.php';
        
        if (!file_exists($file)) {
            throw new \Exception("Arquivo de migration não encontrado: {$file}");
        }

        require_once $file;

        $className = $this->getMigrationClassName($migration);
        
        if (!class_exists($className)) {
            throw new \Exception("Classe de migration não encontrada: {$className}");
        }

        $instance = new $className($this->pdo);

        // Executa o rollback
        $instance->down();
        
        // Se for a migração que cria a tabela migrations, não tenta remover o registro
        if ($migration === '2025_06_04_181808_create_migrations_table') {
            return;
        }
        
        // Remove o registro da migração
        if ($this->hasTable($this->migrationsTable)) {
            $stmt = $this->pdo->prepare(
                "DELETE FROM {$this->migrationsTable} WHERE migration = ?"
            );
            $stmt->execute([$migration]);
        }
    }

    public function getStatus(): array {
        $allMigrations = $this->getAllMigrations();
        $executedMigrations = $this->getExecutedMigrations();
        
        $status = [];
        
        foreach ($allMigrations as $migration) {
            // Se for a migração que cria a tabela migrations e ela já foi executada
            if ($migration === '2025_06_04_181808_create_migrations_table' && $this->hasTable($this->migrationsTable)) {
                $executedAt = $this->getMigrationExecutedAt($migration);
                if (!$executedAt) {
                    // Extrai a data do nome do arquivo (formato: YYYY_MM_DD_HHMMSS)
                    $dateStr = substr($migration, 0, 19);
                    $date = \DateTime::createFromFormat('Y_m_d_His', $dateStr);
                    $executedAt = $date ? $date->format('Y-m-d H:i:s') : null;
                }

                $status[$migration] = [
                    'migration' => $migration,
                    'status' => 'Executada',
                    'batch' => 1,
                    'executed_at' => $executedAt
                ];
                continue;
            }
            
            $status[$migration] = [
                'migration' => $migration,
                'status' => in_array($migration, $executedMigrations) ? 'Executada' : 'Pendente',
                'batch' => $this->getMigrationBatch($migration),
                'executed_at' => $this->getMigrationExecutedAt($migration)
            ];
        }
        
        return $status;
    }

    private function getMigrationBatch(string $migration): ?int {
        if (!$this->hasTable($this->migrationsTable)) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            "SELECT batch FROM {$this->migrationsTable} WHERE migration = ?"
        );
        $stmt->execute([$migration]);
        return $stmt->fetchColumn() ?: null;
    }

    private function getMigrationExecutedAt(string $migration): ?string {
        if (!$this->hasTable($this->migrationsTable)) {
            return null;
        }

        $stmt = $this->pdo->prepare(
            "SELECT executed_at FROM {$this->migrationsTable} WHERE migration = ?"
        );
        $stmt->execute([$migration]);
        return $stmt->fetchColumn() ?: null;
    }

    /**
     * Verifica se uma tabela existe
     */
    public function hasTable(string $table): bool {
        $sql = "SELECT COUNT(*) FROM information_schema.tables WHERE table_name = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$table]);
        return (bool) $stmt->fetchColumn();
    }
} 