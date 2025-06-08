<?php

namespace LadyPHP\Database\ORM\Relations;

use LadyPHP\Database\ORM\Model;

class MorphOne extends MorphTo
{
    /**
     * Retorna os resultados do relacionamento
     */
    public function getResults(): ?Model
    {
        return $this->getRelationQuery()->first();
    }

    /**
     * Carrega o relacionamento de forma eager
     */
    public function getEager(): array
    {
        $models = $this->getRelationQuery()->get();
        
        $dictionary = [];
        foreach ($models as $model) {
            $key = $model->getAttribute($this->foreignKey);
            if (!isset($dictionary[$key])) {
                $dictionary[$key] = $model;
            }
        }

        return $dictionary;
    }

    /**
     * Inicializa o relacionamento em uma coleção de modelos
     */
    public function initRelation(array $models, string $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, null);
        }

        return $models;
    }

    /**
     * Retorna o valor padrão para o relacionamento
     */
    protected function getDefaultFor(Model $model): ?Model
    {
        return null;
    }

    /**
     * Adiciona restrições à query do relacionamento
     */
    public function addConstraints(): void
    {
        if (static::$constraints) {
            $this->getRelationQuery()
                ->where($this->foreignKey, '=', $this->getParentKey())
                ->where($this->morphType, '=', $this->getMorphTypeFromClass(get_class($this->parent)));
        }
    }

    /**
     * Adiciona restrições à query do relacionamento para eager loading
     */
    public function addEagerConstraints(array $models): void
    {
        $this->getRelationQuery()
            ->whereIn($this->foreignKey, $this->getKeys($models, $this->parentKey))
            ->where($this->morphType, '=', $this->getMorphTypeFromClass(get_class($this->parent)));
    }

    /**
     * Cria um novo modelo relacionado
     */
    public function create(array $attributes = []): Model
    {
        $instance = $this->related->newInstance($attributes);
        $instance->setAttribute($this->foreignKey, $this->getParentKey());
        $instance->setAttribute($this->morphType, $this->getMorphTypeFromClass(get_class($this->parent)));
        $instance->save();

        return $instance;
    }

    /**
     * Salva um modelo relacionado
     */
    public function save(Model $model): Model
    {
        $model->setAttribute($this->foreignKey, $this->getParentKey());
        $model->setAttribute($this->morphType, $this->getMorphTypeFromClass(get_class($this->parent)));
        $model->save();

        return $model;
    }
} 