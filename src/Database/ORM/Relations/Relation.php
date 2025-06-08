<?php

namespace LadyPHP\Database\ORM\Relations;

use LadyPHP\Database\ORM\Model;
use LadyPHP\Database\ORM\QueryBuilder;

abstract class Relation implements RelationInterface
{
    protected Model $parent;
    protected string $related;
    protected string $foreignKey;
    protected string $localKey;
    protected QueryBuilder $query;

    public function __construct(Model $parent, string $related, string $foreignKey, string $localKey)
    {
        $this->parent = $parent;
        $this->related = $related;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
        $this->query = (new $related)->newQuery();
        
        $this->addConstraints();
    }

    /**
     * Retorna a query do relacionamento
     */
    public function getRelationQuery(): QueryBuilder
    {
        return $this->query;
    }

    /**
     * Adiciona restrições à query do relacionamento
     */
    public function addConstraints(): void
    {
        if (static::$constraints) {
            $this->getRelationQuery()->where(
                $this->foreignKey, '=', $this->getParentKey()
            );
        }
    }

    /**
     * Adiciona restrições à query do relacionamento para eager loading
     */
    public function addEagerConstraints(array $models): void
    {
        $this->getRelationQuery()->whereIn(
            $this->foreignKey, $this->getKeys($models, $this->localKey)
        );
    }

    /**
     * Inicializa o relacionamento em uma coleção de modelos
     */
    public function initRelation(array $models, string $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->getDefaultFor($model));
        }

        return $models;
    }

    /**
     * Retorna o valor padrão para o relacionamento
     */
    protected function getDefaultFor(Model $model)
    {
        return $this->related::newModelInstance();
    }

    /**
     * Retorna a chave do modelo pai
     */
    protected function getParentKey()
    {
        return $this->parent->getAttribute($this->localKey);
    }

    /**
     * Retorna as chaves de uma coleção de modelos
     */
    protected function getKeys(array $models, string $key): array
    {
        return collect($models)->map(function ($model) use ($key) {
            return $model->getAttribute($key);
        })->values()->unique()->filter()->all();
    }

    /**
     * Retorna o modelo relacionado
     */
    protected function getRelated(): Model
    {
        return new $this->related;
    }

    /**
     * Retorna o modelo pai
     */
    protected function getParent(): Model
    {
        return $this->parent;
    }
} 