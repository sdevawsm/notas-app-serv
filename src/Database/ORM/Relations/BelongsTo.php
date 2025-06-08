<?php

namespace LadyPHP\Database\ORM\Relations;

use LadyPHP\Database\ORM\Model;

class BelongsTo extends Relation
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
            $key = $model->getAttribute($this->ownerKey);
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
                ->where($this->ownerKey, '=', $this->getParentKey());
        }
    }

    /**
     * Adiciona restrições à query do relacionamento para eager loading
     */
    public function addEagerConstraints(array $models): void
    {
        $this->getRelationQuery()
            ->whereIn($this->ownerKey, $this->getKeys($models, $this->foreignKey));
    }

    /**
     * Associa um modelo ao relacionamento
     */
    public function associate(Model $model): void
    {
        $this->parent->setAttribute($this->foreignKey, $model->getKey());
    }

    /**
     * Remove a associação do relacionamento
     */
    public function dissociate(): void
    {
        $this->parent->setAttribute($this->foreignKey, null);
    }

    /**
     * Retorna o nome qualificado da chave do dono
     */
    protected function getQualifiedOwnerKeyName(): string
    {
        return $this->related . '.' . $this->ownerKey;
    }
} 