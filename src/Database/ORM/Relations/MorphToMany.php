<?php

namespace LadyPHP\Database\ORM\Relations;

use LadyPHP\Database\ORM\Model;

class MorphToMany extends BelongsToMany
{
    protected string $morphType;
    protected array $morphMap = [];

    public function __construct(
        Model $parent,
        string $related,
        string $table,
        string $foreignPivotKey,
        string $relatedPivotKey,
        string $parentKey = null,
        string $relatedKey = null,
        string $relation = null
    ) {
        $this->morphType = $relation . '_type';
        $this->morphMap = $parent->getMorphMap();

        parent::__construct($parent, $related, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey);
    }

    /**
     * Adiciona restrições à query do relacionamento
     */
    public function addConstraints(): void
    {
        if (static::$constraints) {
            $this->getRelationQuery()
                ->join($this->table, $this->getQualifiedRelatedKeyName(), '=', $this->getQualifiedRelatedPivotKeyName())
                ->where($this->getQualifiedForeignPivotKeyName(), '=', $this->getParentKey())
                ->where($this->table . '.' . $this->morphType, '=', $this->getMorphTypeFromClass(get_class($this->parent)));
        }
    }

    /**
     * Adiciona restrições à query do relacionamento para eager loading
     */
    public function addEagerConstraints(array $models): void
    {
        $this->getRelationQuery()
            ->join($this->table, $this->getQualifiedRelatedKeyName(), '=', $this->getQualifiedRelatedPivotKeyName())
            ->whereIn($this->getQualifiedForeignPivotKeyName(), $this->getKeys($models, $this->parentKey))
            ->where($this->table . '.' . $this->morphType, '=', $this->getMorphTypeFromClass(get_class($this->parent)));
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
                $this->morphType => $this->getMorphTypeFromClass(get_class($this->parent)),
            ])
        );
    }

    /**
     * Remove um modelo do relacionamento
     */
    public function detach($ids = null): int
    {
        $query = $this->parent->getConnection()->table($this->table)
            ->where($this->foreignPivotKey, $this->getParentKey())
            ->where($this->morphType, $this->getMorphTypeFromClass(get_class($this->parent)));

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
            ->where($this->morphType, $this->getMorphTypeFromClass(get_class($this->parent)))
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

    /**
     * Retorna o tipo morfável a partir da classe do modelo
     */
    protected function getMorphTypeFromClass(string $class): string
    {
        $map = array_flip($this->morphMap);
        return $map[$class] ?? $class;
    }
} 