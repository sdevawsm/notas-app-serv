<?php

namespace LadyPHP\Database\ORM\Relations;

use LadyPHP\Database\ORM\Model;

class MorphTo extends Relation
{
    protected string $morphType;
    protected array $morphMap = [];

    public function __construct(
        Model $parent,
        string $related,
        string $foreignKey,
        string $ownerKey,
        string $type,
        string $relation
    ) {
        $this->morphType = $type;
        $this->morphMap = $parent->getMorphMap();

        parent::__construct($parent, $related, $foreignKey, $ownerKey);
    }

    /**
     * Retorna os resultados do relacionamento
     */
    public function getResults(): ?Model
    {
        if (!$this->foreignKey) {
            return null;
        }

        $type = $this->parent->getAttribute($this->morphType);
        $id = $this->parent->getAttribute($this->foreignKey);

        if (!$type || !$id) {
            return null;
        }

        $class = $this->getMorphClassFromType($type);
        return $class::find($id);
    }

    /**
     * Carrega o relacionamento de forma eager
     */
    public function getEager(): array
    {
        $models = $this->getRelationQuery()->get();
        
        $dictionary = [];
        foreach ($models as $model) {
            $type = $model->getAttribute($this->morphType);
            $id = $model->getAttribute($this->foreignKey);
            
            if ($type && $id) {
                $class = $this->getMorphClassFromType($type);
                if (!isset($dictionary[$class])) {
                    $dictionary[$class] = [];
                }
                $dictionary[$class][] = $id;
            }
        }

        $results = [];
        foreach ($dictionary as $class => $ids) {
            $results[$class] = $class::whereIn($this->ownerKey, $ids)->get();
        }

        return $results;
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
            $type = $this->parent->getAttribute($this->morphType);
            $id = $this->parent->getAttribute($this->foreignKey);

            if ($type && $id) {
                $class = $this->getMorphClassFromType($type);
                $this->getRelationQuery()
                    ->where($this->ownerKey, '=', $id);
            }
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
     * Retorna a classe do modelo a partir do tipo morfável
     */
    protected function getMorphClassFromType(string $type): string
    {
        return $this->morphMap[$type] ?? $type;
    }

    /**
     * Associa um modelo ao relacionamento
     */
    public function associate(Model $model): void
    {
        $this->parent->setAttribute($this->foreignKey, $model->getKey());
        $this->parent->setAttribute($this->morphType, $this->getMorphTypeFromClass(get_class($model)));
    }

    /**
     * Remove a associação do relacionamento
     */
    public function dissociate(): void
    {
        $this->parent->setAttribute($this->foreignKey, null);
        $this->parent->setAttribute($this->morphType, null);
    }

    /**
     * Retorna o tipo morfável a partir da classe do modelo
     */
    protected function getMorphTypeFromClass(string $class): string
    {
        $map = array_flip($this->morphMap);
        return $map[$class] ?? $class;
    }
} 