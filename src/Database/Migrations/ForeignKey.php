<?php

namespace LadyPHP\Database\Migrations;

class ForeignKey
{
    protected array $columns;
    protected ?string $onTable = null;
    protected ?array $onColumns = null;
    protected ?string $onDelete = null;
    protected ?string $onUpdate = null;

    public function __construct(array $columns)
    {
        $this->columns = $columns;
    }

    /**
     * Define a tabela referenciada
     */
    public function references(string|array $columns): self
    {
        $this->onColumns = (array) $columns;
        return $this;
    }

    /**
     * Define a tabela referenciada
     */
    public function on(string $table): self
    {
        $this->onTable = $table;
        return $this;
    }

    /**
     * Define a ação on delete
     */
    public function onDelete(string $action): self
    {
        $this->onDelete = $action;
        return $this;
    }

    /**
     * Define a ação on update
     */
    public function onUpdate(string $action): self
    {
        $this->onUpdate = $action;
        return $this;
    }

    /**
     * Gera o SQL da chave estrangeira
     */
    public function toSql(): string
    {
        if (!$this->onTable || !$this->onColumns) {
            throw new \Exception('Foreign key must have a referenced table and columns');
        }

        $columns = implode(', ', $this->columns);
        $onColumns = implode(', ', $this->onColumns);

        $sql = "FOREIGN KEY ({$columns}) REFERENCES {$this->onTable} ({$onColumns})";

        if ($this->onDelete) {
            $sql .= " ON DELETE {$this->onDelete}";
        }

        if ($this->onUpdate) {
            $sql .= " ON UPDATE {$this->onUpdate}";
        }

        return $sql;
    }
} 