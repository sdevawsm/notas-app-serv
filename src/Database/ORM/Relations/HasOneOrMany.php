<?php

namespace LadyPHP\Database\ORM\Relations;

use LadyPHP\Database\ORM\Model;

abstract class HasOneOrMany extends Relation
{
    /**
     * Cria um novo modelo relacionado
     */
    public function create(array $attributes = []): Model
    {
        $instance = $this->related->newInstance($attributes);
        $instance->setAttribute($this->foreignKey, $this->getParentKey());
        $instance->save();

        return $instance;
    }

    /**
     * Salva um modelo relacionado
     */
    public function save(Model $model): Model
    {
        $model->setAttribute($this->foreignKey, $this->getParentKey());
        $model->save();

        return $model;
    }

    /**
     * Adiciona restrições à query do relacionamento
     */
    public function addConstraints(): void
    {
        if (static::$constraints) {
            $this->getRelationQuery()
                ->where($this->foreignKey, '=', $this->getParentKey());
        }
    }

    /**
     * Adiciona restrições à query do relacionamento para eager loading
     */
    public function addEagerConstraints(array $models): void
    {
        $this->getRelationQuery()
            ->whereIn($this->foreignKey, $this->getKeys($models, $this->parentKey));
    }

    /**
     * Retorna o nome qualificado da chave estrangeira
     */
    protected function getQualifiedForeignKeyName(): string
    {
        return $this->related . '.' . $this->foreignKey;
    }
} 