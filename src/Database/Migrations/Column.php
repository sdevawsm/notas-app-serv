<?php

namespace LadyPHP\Database\Migrations;

class Column
{
    protected string $type;
    protected string $name;
    protected array $parameters;
    protected bool $nullable = false;
    protected bool $autoIncrement = false;
    protected $default = null;
    protected bool $unsigned = false;

    public function __construct(string $type, string $name, array $parameters = [])
    {
        $this->type = $type;
        $this->name = $name;
        $this->parameters = $parameters;
    }

    /**
     * Define a coluna como nullable
     */
    public function nullable(): self
    {
        $this->nullable = true;
        return $this;
    }

    /**
     * Define a coluna como auto incremento
     */
    public function autoIncrement(): self
    {
        $this->autoIncrement = true;
        return $this;
    }

    /**
     * Define o valor padrão da coluna
     */
    public function default($value): self
    {
        $this->default = $value;
        return $this;
    }

    /**
     * Define a coluna como unsigned
     */
    public function unsigned(): self
    {
        $this->unsigned = true;
        return $this;
    }

    /**
     * Gera o SQL da coluna
     */
    public function toSql(): string
    {
        $sql = "{$this->name} {$this->getType()}";

        if ($this->unsigned) {
            $sql .= ' UNSIGNED';
        }

        if ($this->autoIncrement) {
            $sql .= ' AUTO_INCREMENT';
        }

        if ($this->nullable) {
            $sql .= ' NULL';
        } else {
            $sql .= ' NOT NULL';
        }

        if ($this->default !== null) {
            $sql .= " DEFAULT " . $this->getDefaultValue();
        }

        return $sql;
    }

    /**
     * Retorna o tipo da coluna
     */
    protected function getType(): string
    {
        return match($this->type) {
            'string' => "VARCHAR({$this->parameters['length']})",
            'text' => 'TEXT',
            'integer' => 'INT',
            'bigInteger' => 'BIGINT',
            'unsignedBigInteger' => 'BIGINT UNSIGNED',
            'boolean' => 'BOOLEAN',
            'date' => 'DATE',
            'datetime' => 'DATETIME',
            'timestamp' => 'TIMESTAMP',
            default => $this->type
        };
    }

    /**
     * Retorna o valor padrão formatado
     */
    protected function getDefaultValue(): string
    {
        if (is_string($this->default)) {
            return "'{$this->default}'";
        }

        if (is_bool($this->default)) {
            return $this->default ? '1' : '0';
        }

        if (is_null($this->default)) {
            return 'NULL';
        }

        return (string) $this->default;
    }
} 