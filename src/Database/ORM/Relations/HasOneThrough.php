<?php

namespace LadyPHP\Database\ORM\Relations;

use LadyPHP\Database\ORM\Model;

class HasOneThrough extends HasManyThrough
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
            $key = $model->getAttribute($this->firstKey);
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
} 