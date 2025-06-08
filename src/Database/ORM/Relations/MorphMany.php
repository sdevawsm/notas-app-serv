<?php

namespace LadyPHP\Database\ORM\Relations;

use LadyPHP\Database\ORM\Model;

class MorphMany extends MorphOne
{
    /**
     * Retorna os resultados do relacionamento
     */
    public function getResults(): array
    {
        return $this->getRelationQuery()->get();
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
                $dictionary[$key] = [];
            }
            $dictionary[$key][] = $model;
        }

        return $dictionary;
    }

    /**
     * Inicializa o relacionamento em uma coleção de modelos
     */
    public function initRelation(array $models, string $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, []);
        }

        return $models;
    }

    /**
     * Retorna o valor padrão para o relacionamento
     */
    protected function getDefaultFor(Model $model): array
    {
        return [];
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