<?php

namespace LadyPHP\Database\Migrations;

class Blueprint
{
    protected string $table;
    protected bool $isModify;
    protected array $columns = [];
    protected array $indexes = [];
    protected array $foreignKeys = [];
    protected array $primaryKeys = [];
    protected array $uniqueKeys = [];
    protected ?string $lastColumn = null;

    public function __construct(string $table, bool $isModify = false)
    {
        $this->table = $table;
        $this->isModify = $isModify;
    }

    /**
     * Adiciona uma coluna id
     */
    public function id(): self
    {
        return $this->bigIncrements('id');
    }

    /**
     * Adiciona uma coluna bigIncrements
     */
    public function bigIncrements(string $column): self
    {
        return $this->unsignedBigInteger($column, true);
    }

    /**
     * Adiciona uma coluna bigInteger
     */
    public function bigInteger(string $column, bool $autoIncrement = false): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'BIGINT',
            'autoIncrement' => $autoIncrement,
            'unsigned' => $autoIncrement,
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna unsignedBigInteger
     */
    public function unsignedBigInteger(string $column, bool $autoIncrement = false): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'BIGINT UNSIGNED',
            'autoIncrement' => $autoIncrement,
            'unsigned' => true,
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna integer
     */
    public function integer(string $column, bool $autoIncrement = false): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'INT',
            'autoIncrement' => $autoIncrement,
            'unsigned' => $autoIncrement,
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna unsignedInteger
     */
    public function unsignedInteger(string $column, bool $autoIncrement = false): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'INT UNSIGNED',
            'autoIncrement' => $autoIncrement,
            'unsigned' => true,
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna tinyInteger
     */
    public function tinyInteger(string $column, bool $autoIncrement = false): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'TINYINT',
            'autoIncrement' => $autoIncrement,
            'unsigned' => $autoIncrement,
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna unsignedTinyInteger
     */
    public function unsignedTinyInteger(string $column, bool $autoIncrement = false): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'TINYINT UNSIGNED',
            'autoIncrement' => $autoIncrement,
            'unsigned' => true,
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna smallInteger
     */
    public function smallInteger(string $column, bool $autoIncrement = false): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'SMALLINT',
            'autoIncrement' => $autoIncrement,
            'unsigned' => $autoIncrement,
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna unsignedSmallInteger
     */
    public function unsignedSmallInteger(string $column, bool $autoIncrement = false): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'SMALLINT UNSIGNED',
            'autoIncrement' => $autoIncrement,
            'unsigned' => true,
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna mediumInteger
     */
    public function mediumInteger(string $column, bool $autoIncrement = false): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'MEDIUMINT',
            'autoIncrement' => $autoIncrement,
            'unsigned' => $autoIncrement,
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna unsignedMediumInteger
     */
    public function unsignedMediumInteger(string $column, bool $autoIncrement = false): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'MEDIUMINT UNSIGNED',
            'autoIncrement' => $autoIncrement,
            'unsigned' => true,
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna decimal
     */
    public function decimal(string $column, int $total = 8, int $places = 2): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => "DECIMAL({$total}, {$places})",
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna float
     */
    public function float(string $column, int $total = 8, int $places = 2): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => "FLOAT({$total}, {$places})",
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna double
     */
    public function double(string $column, int $total = 8, int $places = 2): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => "DOUBLE({$total}, {$places})",
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna date
     */
    public function date(string $column): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'DATE',
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna datetime
     */
    public function datetime(string $column): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'DATETIME',
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna time
     */
    public function time(string $column): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'TIME',
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna year
     */
    public function year(string $column): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'YEAR',
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna json
     */
    public function json(string $column): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'JSON',
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna enum
     */
    public function enum(string $column, array $allowed): self
    {
        $values = array_map(function($value) {
            return "'{$value}'";
        }, $allowed);

        $this->columns[] = [
            'name' => $column,
            'type' => 'ENUM(' . implode(', ', $values) . ')',
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna set
     */
    public function set(string $column, array $allowed): self
    {
        $values = array_map(function($value) {
            return "'{$value}'";
        }, $allowed);

        $this->columns[] = [
            'name' => $column,
            'type' => 'SET(' . implode(', ', $values) . ')',
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna binary
     */
    public function binary(string $column): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'BINARY',
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna varbinary
     */
    public function varbinary(string $column, int $length = 255): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => "VARBINARY({$length})",
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna tinyblob
     */
    public function tinyblob(string $column): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'TINYBLOB',
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna blob
     */
    public function blob(string $column): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'BLOB',
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna mediumblob
     */
    public function mediumblob(string $column): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'MEDIUMBLOB',
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna longblob
     */
    public function longblob(string $column): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'LONGBLOB',
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna tinytext
     */
    public function tinytext(string $column): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'TINYTEXT',
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna mediumtext
     */
    public function mediumtext(string $column): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'MEDIUMTEXT',
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna longtext
     */
    public function longtext(string $column): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'LONGTEXT',
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna char
     */
    public function char(string $column, int $length = 255): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => "CHAR({$length})",
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna point
     */
    public function point(string $column): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'POINT',
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna linestring
     */
    public function linestring(string $column): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'LINESTRING',
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna polygon
     */
    public function polygon(string $column): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'POLYGON',
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna geometry
     */
    public function geometry(string $column): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'GEOMETRY',
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna geometrycollection
     */
    public function geometrycollection(string $column): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'GEOMETRYCOLLECTION',
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna multipoint
     */
    public function multipoint(string $column): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'MULTIPOINT',
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna multilinestring
     */
    public function multilinestring(string $column): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'MULTILINESTRING',
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna multipolygon
     */
    public function multipolygon(string $column): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'MULTIPOLYGON',
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna softDeletes
     */
    public function softDeletes(): self
    {
        $this->columns[] = [
            'name' => 'deleted_at',
            'type' => 'TIMESTAMP',
            'nullable' => true,
            'default' => null
        ];
        return $this;
    }

    /**
     * Adiciona uma coluna string
     */
    public function string(string $column, int $length = 255): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => "VARCHAR({$length})",
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna text
     */
    public function text(string $column): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'TEXT',
            'nullable' => false,
            'default' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna boolean
     */
    public function boolean(string $column): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'BOOLEAN',
            'nullable' => false,
            'default' => false
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona uma coluna timestamp
     */
    public function timestamp(string $column): self
    {
        $this->columns[] = [
            'name' => $column,
            'type' => 'TIMESTAMP',
            'nullable' => false,
            'default' => null,
            'useCurrent' => false,
            'onUpdate' => false
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Adiciona timestamps created_at e updated_at
     */
    public function timestamps(): self
    {
        // created_at: DEFAULT CURRENT_TIMESTAMP
        $this->columns[] = [
            'name' => 'created_at',
            'type' => 'TIMESTAMP',
            'nullable' => false,
            'default' => null,
            'useCurrent' => true,
            'onUpdate' => false
        ];

        // updated_at: DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        $this->columns[] = [
            'name' => 'updated_at',
            'type' => 'TIMESTAMP',
            'nullable' => false,
            'default' => null,
            'useCurrent' => true,
            'onUpdate' => true
        ];

        return $this;
    }

    /**
     * Define uma coluna como nullable
     */
    public function nullable(): self
    {
        if ($this->lastColumn) {
            foreach ($this->columns as &$column) {
                if ($column['name'] === $this->lastColumn) {
                    $column['nullable'] = true;
                    break;
                }
            }
        }
        return $this;
    }

    /**
     * Define o valor padrão de uma coluna
     */
    public function default($value): self
    {
        if ($this->lastColumn) {
            foreach ($this->columns as &$column) {
                if ($column['name'] === $this->lastColumn) {
                    $column['default'] = $value;
                    break;
                }
            }
        }
        return $this;
    }

    /**
     * Define uma coluna para usar CURRENT_TIMESTAMP
     */
    public function useCurrent(): self
    {
        if ($this->lastColumn) {
            foreach ($this->columns as &$column) {
                if ($column['name'] === $this->lastColumn) {
                    $column['useCurrent'] = true;
                    break;
                }
            }
        }
        return $this;
    }

    /**
     * Define uma coluna para usar ON UPDATE CURRENT_TIMESTAMP
     */
    public function onUpdateCurrentTimestamp(): self
    {
        if ($this->lastColumn) {
            foreach ($this->columns as &$column) {
                if ($column['name'] === $this->lastColumn) {
                    $column['onUpdate'] = true;
                    break;
                }
            }
        }
        return $this;
    }

    /**
     * Define uma coluna como unique
     */
    public function unique(): self
    {
        if ($this->lastColumn) {
            $this->uniqueKeys[] = $this->lastColumn;
        }
        return $this;
    }

    /**
     * Define uma chave estrangeira
     */
    public function foreign(string $column): self
    {
        $this->foreignKeys[] = [
            'column' => $column,
            'on' => null,
            'references' => null,
            'onDelete' => null,
            'onUpdate' => null
        ];
        $this->lastColumn = $column;
        return $this;
    }

    /**
     * Define a tabela referenciada pela chave estrangeira
     */
    public function references(string $column): self
    {
        if (!empty($this->foreignKeys)) {
            $this->foreignKeys[count($this->foreignKeys) - 1]['references'] = $column;
        }
        return $this;
    }

    /**
     * Define a tabela referenciada pela chave estrangeira
     */
    public function on(string $table): self
    {
        if (!empty($this->foreignKeys)) {
            $this->foreignKeys[count($this->foreignKeys) - 1]['on'] = $table;
        }
        return $this;
    }

    /**
     * Define a ação onDelete da chave estrangeira
     */
    public function onDelete(string $action): self
    {
        if (!empty($this->foreignKeys)) {
            $this->foreignKeys[count($this->foreignKeys) - 1]['onDelete'] = $action;
        }
        return $this;
    }

    /**
     * Define a ação onUpdate da chave estrangeira
     */
    public function onUpdate(string $action): self
    {
        if (!empty($this->foreignKeys)) {
            $this->foreignKeys[count($this->foreignKeys) - 1]['onUpdate'] = $action;
        }
        return $this;
    }

    /**
     * Gera o SQL para criar ou modificar a tabela
     */
    public function toSql(): string
    {
        if ($this->isModify) {
            return $this->toModifySql();
        }
        return $this->toCreateSql();
    }

    /**
     * Gera o SQL para criar a tabela
     */
    protected function toCreateSql(): string
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (";
        $columns = [];

        foreach ($this->columns as $column) {
            $columnSql = "`{$column['name']}` {$column['type']}";
            
            if ($column['autoIncrement'] ?? false) {
                $columnSql .= ' AUTO_INCREMENT';
            }
            
            if (!($column['nullable'] ?? false)) {
                $columnSql .= ' NOT NULL';
            }

            if ($column['useCurrent'] ?? false) {
                $columnSql .= ' DEFAULT CURRENT_TIMESTAMP';
                if ($column['onUpdate'] ?? false) {
                    $columnSql .= ' ON UPDATE CURRENT_TIMESTAMP';
                }
            } elseif ($column['default'] !== null) {
                $columnSql .= " DEFAULT '{$column['default']}'";
            }

            $columns[] = $columnSql;
        }

        // Adiciona as chaves primárias
        $primaryKeys = array_filter($this->columns, fn($col) => $col['autoIncrement'] ?? false);
        if (!empty($primaryKeys)) {
            $columns[] = 'PRIMARY KEY (' . implode(', ', array_map(fn($col) => "`{$col['name']}`", $primaryKeys)) . ')';
        }

        // Adiciona os índices únicos
        foreach ($this->uniqueKeys as $column) {
            $columns[] = "UNIQUE KEY `uk_{$this->table}_{$column}` (`{$column}`)";
        }

        // Adiciona as chaves estrangeiras
        foreach ($this->foreignKeys as $fk) {
            if ($fk['on'] && $fk['references']) {
                $columns[] = "CONSTRAINT `fk_{$this->table}_{$fk['column']}` " .
                           "FOREIGN KEY (`{$fk['column']}`) " .
                           "REFERENCES `{$fk['on']}` (`{$fk['references']}`)" .
                           ($fk['onDelete'] ? " ON DELETE {$fk['onDelete']}" : '') .
                           ($fk['onUpdate'] ? " ON UPDATE {$fk['onUpdate']}" : '');
            }
        }

        $sql .= implode(', ', $columns) . ')';
        return $sql;
    }

    /**
     * Gera o SQL para modificar a tabela
     */
    protected function toModifySql(): string
    {
        $sql = [];
        
        foreach ($this->columns as $column) {
            $columnSql = "ALTER TABLE {$this->table} ADD COLUMN {$column['name']} {$column['type']}";
            
            if (!($column['nullable'] ?? false)) {
                $columnSql .= ' NOT NULL';
            }

            if ($column['useCurrent'] ?? false) {
                $columnSql .= ' DEFAULT CURRENT_TIMESTAMP';
                if ($column['onUpdate'] ?? false) {
                    $columnSql .= ' ON UPDATE CURRENT_TIMESTAMP';
                }
            } elseif ($column['default'] !== null) {
                $columnSql .= " DEFAULT '{$column['default']}'";
            }

            $sql[] = $columnSql;
        }

        foreach ($this->uniqueKeys as $column) {
            $sql[] = "ALTER TABLE {$this->table} ADD UNIQUE KEY `uk_{$this->table}_{$column}` (`{$column}`)";
        }

        foreach ($this->foreignKeys as $fk) {
            if ($fk['on'] && $fk['references']) {
                $sql[] = "ALTER TABLE {$this->table} ADD CONSTRAINT `fk_{$this->table}_{$fk['column']}` " .
                        "FOREIGN KEY (`{$fk['column']}`) REFERENCES `{$fk['on']}` (`{$fk['references']}`)" .
                        ($fk['onDelete'] ? " ON DELETE {$fk['onDelete']}" : '') .
                        ($fk['onUpdate'] ? " ON UPDATE {$fk['onUpdate']}" : '');
            }
        }

        return implode('; ', $sql);
    }
} 