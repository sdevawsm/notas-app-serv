<?php

namespace LadyPHP\Database\ORM\Relations;

interface RelationInterface
{
    /**
     * Retorna os resultados do relacionamento
     */
    public function getResults();

    /**
     * Inicializa o relacionamento em uma coleção de modelos
     */
    public function initRelation(array $models, string $relation): array;

    /**
     * Carrega o relacionamento de forma eager
     */
    public function getEager(): array;

    /**
     * Adiciona restrições à query do relacionamento
     */
    public function addConstraints(): void;

    /**
     * Adiciona restrições à query do relacionamento para eager loading
     */
    public function addEagerConstraints(array $models): void;

    /**
     * Retorna a query do relacionamento
     */
    public function getRelationQuery(): \LadyPHP\Database\ORM\QueryBuilder;
} 