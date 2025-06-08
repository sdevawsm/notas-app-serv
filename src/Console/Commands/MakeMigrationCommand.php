<?php

namespace LadyPHP\Console\Commands;

use LadyPHP\Console\Command;

class MakeMigrationCommand extends Command
{
    protected string $name = 'make:migration';
    protected string $description = 'Cria um novo arquivo de migração';

    public function __construct()
    {
        $this->options = [
            '--name' => 'Nome da migração'
        ];
    }

    public function handle(): int
    {
        $name = $this->getOptionValue('--name');
        
        if (!$name) {
            $this->error('O nome da migração é obrigatório. Use --name para especificar.');
            return 1;
        }

        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_{$name}.php";
        $path = dirname(__DIR__, 3) . '/database/migrations/' . $filename;

        if (file_exists($path)) {
            $this->error("A migração {$filename} já existe.");
            return 1;
        }

        $className = $this->getClassName($name);
        $content = $this->getMigrationTemplate($className);

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $content);
        $this->info("Migração {$filename} criada com sucesso!");
        return 0;
    }

    private function getClassName(string $name): string
    {
        // Remove a data do início do nome do arquivo (formato: YYYY_MM_DD_HHMMSS_)
        $name = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $name);
        
        // Converte para PascalCase
        $name = str_replace('_', ' ', $name);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);
        
        return $name;
    }

    private function getMigrationTemplate(string $className): string
    {
        return <<<PHP
<?php

use LadyPHP\Database\Migrations\Migration;

class {$className} extends Migration
{
    public function up(): void
    {
        \$this->schema->create('table_name', function (\$table) {
            \$table->id();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        \$this->schema->dropIfExists('table_name');
    }
}
PHP;
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