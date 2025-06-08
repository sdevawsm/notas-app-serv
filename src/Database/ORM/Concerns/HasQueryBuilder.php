<?php

namespace LadyPHP\Database\ORM\Concerns;

use LadyPHP\Database\ORM\QueryBuilder;

trait HasQueryBuilder
{
    /**
     * Retorna uma nova query builder
     */
    public static function query(): QueryBuilder
    {
        return (new static)->newQuery();
    }

    /**
     * Retorna uma nova query builder
     */
    public function newQuery(): QueryBuilder
    {
        return new QueryBuilder($this);
    }

    /**
     * Encontra um modelo pelo ID
     */
    public static function find($id): ?static
    {
        return static::query()->find($id);
    }

    /**
     * Encontra um modelo pelo ID ou lança uma exceção
     */
    public static function findOrFail($id): static
    {
        $model = static::find($id);

        if (is_null($model)) {
            throw new \Exception("Model not found");
        }

        return $model;
    }

    /**
     * Retorna o nome da tabela
     */
    public function getTable(): string
    {
        return static::$table ?? strtolower(class_basename($this)) . 's';
    }

    /**
     * Retorna o nome da chave primária
     */
    public function getKeyName(): string
    {
        return $this->primaryKey;
    }

    /**
     * Retorna o valor da chave primária
     */
    public function getKey()
    {
        return $this->getAttribute($this->getKeyName());
    }

    /**
     * Retorna uma nova instância do modelo
     */
    public static function newModelInstance(array $attributes = []): static
    {
        return new static($attributes);
    }
} 