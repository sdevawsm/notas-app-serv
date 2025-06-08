<?php

namespace LadyPHP\Database\ORM\Events;

interface EventInterface
{
    /**
     * Retorna o nome do evento
     */
    public function getName(): string;

    /**
     * Retorna o modelo associado ao evento
     */
    public function getModel(): \LadyPHP\Database\ORM\Model;

    /**
     * Retorna os dados do evento
     */
    public function getData(): array;
} 