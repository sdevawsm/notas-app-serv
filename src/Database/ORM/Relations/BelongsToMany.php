<?php

namespace LadyPHP\Database\ORM\Relations;

use LadyPHP\Database\ORM\Model;

class BelongsToMany extends Relation
{
    protected string $table;
    protected string $foreignPivotKey;
    protected string $relatedPivotKey;
    protected array $pivotColumns = [];
    protected array $pivotValues = [];

    public function __construct(
        Model $parent,
        string $related,
        string $table,
        string $foreignPivotKey,
        string $relatedPivotKey,
        string $parentKey = null,
        string $relatedKey = null
    ) {
        $this->table = $table;
        $this->foreignPivotKey = $foreignPivotKey;
        $this->relatedPivotKey = $relatedPivotKey;
        $this->parentKey = $parentKey ?? $parent->getKeyName();
        $this->relatedKey = $relatedKey ?? (new $related)->getKeyName();

        parent::__construct($parent, $related, $foreignPivotKey, $parentKey);
    }

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
            $key = $model->pivot->getAttribute($this->foreignPivotKey);
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
     * Adiciona restrições à query do relacionamento
     */
    public function addConstraints(): void
    {
        if (static::$constraints) {
            $this->getRelationQuery()
                ->join($this->table, $this->getQualifiedRelatedKeyName(), '=', $this->getQualifiedRelatedPivotKeyName())
                ->where($this->getQualifiedForeignPivotKeyName(), '=', $this->getParentKey());
        }
    }

    /**
     * Adiciona restrições à query do relacionamento para eager loading
     */
    public function addEagerConstraints(array $models): void
    {
        $this->getRelationQuery()
            ->join($this->table, $this->getQualifiedRelatedKeyName(), '=', $this->getQualifiedRelatedPivotKeyName())
            ->whereIn($this->getQualifiedForeignPivotKeyName(), $this->getKeys($models, $this->parentKey));
    }

    /**
     * Retorna o nome qualificado da chave estrangeira do pivot
     */
    protected function getQualifiedForeignPivotKeyName(): string
    {
        return $this->table . '.' . $this->foreignPivotKey;
    }

    /**
     * Retorna o nome qualificado da chave relacionada do pivot
     */
    protected function getQualifiedRelatedPivotKeyName(): string
    {
        return $this->table . '.' . $this->relatedPivotKey;
    }

    /**
     * Retorna o nome qualificado da chave relacionada
     */
    protected function getQualifiedRelatedKeyName(): string
    {
        return $this->related . '.' . $this->relatedKey;
    }

    /**
     * Anexa um modelo ao relacionamento
     */
    public function attach($id, array $attributes = []): void
    {
        $this->parent->getConnection()->table($this->table)->insert(
            array_merge($attributes, [
                $this->foreignPivotKey => $this->getParentKey(),
                $this->relatedPivotKey => $id,
            ])
        );
    }

    /**
     * Remove um modelo do relacionamento
     */
    public function detach($ids = null): int
    {
        $query = $this->parent->getConnection()->table($this->table)
            ->where($this->foreignPivotKey, $this->getParentKey());

        if ($ids !== null) {
            $query->whereIn($this->relatedPivotKey, (array) $ids);
        }

        return $query->delete();
    }

    /**
     * Sincroniza os modelos relacionados
     */
    public function sync($ids): array
    {
        $changes = [
            'attached' => [],
            'detached' => [],
            'updated' => [],
        ];

        $current = $this->parent->getConnection()
            ->table($this->table)
            ->where($this->foreignPivotKey, $this->getParentKey())
            ->pluck($this->relatedPivotKey)
            ->all();

        $detach = array_diff($current, (array) $ids);
        $attach = array_diff((array) $ids, $current);

        if (count($detach) > 0) {
            $this->detach($detach);
            $changes['detached'] = $detach;
        }

        if (count($attach) > 0) {
            $this->attach($attach);
            $changes['attached'] = $attach;
        }

        return $changes;
    }
} 