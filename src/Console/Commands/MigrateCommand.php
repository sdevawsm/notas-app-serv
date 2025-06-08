<?php

namespace LadyPHP\Console\Commands;

use LadyPHP\Console\Command;
use LadyPHP\Database\MigrationManager;
use LadyPHP\Database\Connection\ConnectionManager;
use PDO;

class MigrateCommand extends Command
{
    protected string $name = 'migrate';
    protected string $description = 'Executa as migrações pendentes';

    public function __construct()
    {
        $this->options = [
            '--fresh' => 'Remove todas as tabelas e executa todas as migrações',
            '--refresh' => 'Revoga e executa todas as migrações novamente',
            '--rollback' => 'Revoga a última migração',
            '--reset' => 'Revoga todas as migrações',
            '--step' => 'Número de migrações para revogar',
            '--status' => 'Exibe o status das migrações'
        ];
    }

    public function handle(): int
    {
        $manager = new MigrationManager(ConnectionManager::getInstance()->getPdo());

        // Verifica os argumentos da linha de comando
        $args = $GLOBALS['argv'] ?? [];
        $hasStatus = in_array('--status', $args);
        $hasRollback = in_array('--rollback', $args);
        $hasFresh = in_array('--fresh', $args);
        $hasRefresh = in_array('--refresh', $args);
        $hasReset = in_array('--reset', $args);

        if ($hasStatus) {
            $statusCommand = new MigrateStatusCommand();
            return $statusCommand->handle();
        }

        if ($hasFresh) {
            return $this->fresh($manager);
        }

        if ($hasRefresh) {
            return $this->refresh($manager);
        }

        if ($hasRollback) {
            $step = (int) $this->getOptionValue('--step', 1);
            return $this->rollback($manager, $step);
        }

        if ($hasReset) {
            return $this->reset($manager);
        }

        return $this->migrate($manager);
    }

    protected function migrate(MigrationManager $manager): int
    {
        if (!$manager->hasPendingMigrations()) {
            $this->info('Nenhuma migração pendente.');
            return 0;
        }

        $pendingMigrations = $manager->getPendingMigrations();
        $this->info('Executando migrações pendentes...');

        foreach ($pendingMigrations as $migration) {
            $this->info("Executando migração: {$migration}");
        }

        $manager->runMigrations();
        $this->info('Migrações executadas com sucesso!');
        return 0;
    }

    protected function fresh(MigrationManager $manager): int
    {
        $this->info('Removendo todas as tabelas...');
        
        // Obtém todas as tabelas do banco de dados
        $pdo = ConnectionManager::getInstance()->getPdo();
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        // Desativa a verificação de chaves estrangeiras
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        
        // Remove todas as tabelas
        foreach ($tables as $table) {
            $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
        }
        
        // Reativa a verificação de chaves estrangeiras
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        
        $this->info('Todas as tabelas foram removidas com sucesso!');
        return 0;
    }

    protected function refresh(MigrationManager $manager): int
    {
        $this->info('Revogando todas as migrações...');
        $manager->rollback();
        $this->info('Rollback concluído com sucesso!');
        return 0;
    }

    protected function rollback(MigrationManager $manager, int $step = 1): int
    {
        $this->info("Revogando as últimas {$step} migrações...");
        $manager->rollback($step);
        $this->info("Rollback concluído com sucesso!");
        return 0;
    }

    protected function reset(MigrationManager $manager): int
    {
        $this->info('Revogando todas as migrações...');
        $manager->rollback();
        return 0;
    }

    protected function hasOption(string $option): bool
    {
        return in_array($option, $this->options);
    }

    protected function getOptionValue(string $option, $default = null)
    {
        $args = $GLOBALS['argv'] ?? [];
        $index = array_search($option, $args);
        
        if ($index === false) {
            return $default;
        }

        // Verifica se o próximo argumento existe e não é uma opção
        if (isset($args[$index + 1]) && !str_starts_with($args[$index + 1], '--')) {
            return $args[$index + 1];
        }

        return $default;
    }
} 