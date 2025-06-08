<?php

namespace LadyPHP\Database\ORM\Relations;

use LadyPHP\Database\ORM\Model;

class MorphByMany extends MorphToMany
{
    /**
     * Adiciona restrições à query do relacionamento
     */
    public function addConstraints(): void
    {
        if (static::$constraints) {
            $this->getRelationQuery()
                ->join($this->table, $this->getQualifiedRelatedKeyName(), '=', $this->getQualifiedRelatedPivotKeyName())
                ->where($this->getQualifiedForeignPivotKeyName(), '=', $this->getParentKey())
                ->where($this->table . '.' . $this->morphType, '=', $this->getMorphTypeFromClass(get_class($this->related)));
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
            ->where($this->table . '.' . $this->morphType, '=', $this->getMorphTypeFromClass(get_class($this->related)));
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
                $this->morphType => $this->getMorphTypeFromClass(get_class($this->related)),
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
            ->where($this->morphType, $this->getMorphTypeFromClass(get_class($this->related)));

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
            ->where($this->morphType, $this->getMorphTypeFromClass(get_class($this->related)))
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