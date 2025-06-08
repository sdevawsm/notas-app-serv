<?php

namespace LadyPHP\Console\Commands;

use LadyPHP\Console\Command;
use LadyPHP\Database\MigrationManager;
use LadyPHP\Database\Connection\ConnectionManager;

class MigrateStatusCommand extends Command
{
    protected string $name = 'migrate:status';
    protected string $description = 'Exibe o status das migrações';

    public function handle(): int
    {
        $manager = new MigrationManager(ConnectionManager::getInstance()->getPdo());
        $status = $manager->getStatus();

        $this->info("\nStatus das Migrações:");
        $this->line("===================\n");

        $headers = ['Migration', 'Status', 'Batch', 'Executado em'];
        $rows = [];

        foreach ($status as $migration) {
            $rows[] = [
                $migration['migration'],
                $migration['status'],
                $migration['batch'] ?? '-',
                $migration['executed_at'] ?? '-'
            ];
        }

        // Calcula o tamanho máximo de cada coluna
        $columns = [$headers];
        foreach ($rows as $row) {
            $columns[] = $row;
        }
        $colWidths = [];
        for ($i = 0; $i < count($headers); $i++) {
            $colWidths[$i] = max(array_map(fn($col) => mb_strlen((string)$col[$i]), $columns));
        }

        // Função para formatar uma linha
        $formatRow = function($row) use ($colWidths) {
            $out = '';
            foreach ($row as $i => $col) {
                $out .= str_pad((string)$col, $colWidths[$i] + 2);
            }
            return rtrim($out);
        };

        // Exibe cabeçalho
        $this->line($formatRow($headers));
        $this->line(str_repeat('-', array_sum($colWidths) + count($colWidths) * 2));
        // Exibe linhas
        foreach ($rows as $row) {
            $this->line($formatRow($row));
        }
        return 0;
    }
} 