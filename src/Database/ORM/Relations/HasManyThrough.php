<?php

namespace LadyPHP\Database\ORM\Relations;

use LadyPHP\Database\ORM\Model;

class HasManyThrough extends Relation
{
    protected string $throughParent;
    protected string $firstKey;
    protected string $secondKey;
    protected string $localKey;
    protected string $secondLocalKey;

    public function __construct(
        Model $parent,
        string $related,
        string $through,
        string $firstKey,
        string $secondKey,
        string $localKey = null,
        string $secondLocalKey = null
    ) {
        $this->throughParent = $through;
        $this->firstKey = $firstKey;
        $this->secondKey = $secondKey;
        $this->localKey = $localKey ?? $parent->getKeyName();
        $this->secondLocalKey = $secondLocalKey ?? (new $through)->getKeyName();

        parent::__construct($parent, $related, $firstKey, $localKey);
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
            $key = $model->getAttribute($this->firstKey);
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
                ->join($this->throughParent, $this->getQualifiedFirstKeyName(), '=', $this->getQualifiedSecondKeyName())
                ->where($this->getQualifiedLocalKeyName(), '=', $this->getParentKey());
        }
    }

    /**
     * Adiciona restrições à query do relacionamento para eager loading
     */
    public function addEagerConstraints(array $models): void
    {
        $this->getRelationQuery()
            ->join($this->throughParent, $this->getQualifiedFirstKeyName(), '=', $this->getQualifiedSecondKeyName())
            ->whereIn($this->getQualifiedLocalKeyName(), $this->getKeys($models, $this->localKey));
    }

    /**
     * Retorna o nome qualificado da primeira chave
     */
    protected function getQualifiedFirstKeyName(): string
    {
        return $this->throughParent . '.' . $this->firstKey;
    }

    /**
     * Retorna o nome qualificado da segunda chave
     */
    protected function getQualifiedSecondKeyName(): string
    {
        return $this->related . '.' . $this->secondKey;
    }

    /**
     * Retorna o nome qualificado da chave local
     */
    protected function getQualifiedLocalKeyName(): string
    {
        return $this->throughParent . '.' . $this->secondLocalKey;
    }
} 