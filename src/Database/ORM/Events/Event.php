<?php

namespace LadyPHP\Database\ORM\Events;

use LadyPHP\Database\ORM\Model;

abstract class Event implements EventInterface
{
    protected Model $model;
    protected array $data;

    public function __construct(Model $model, array $data = [])
    {
        $this->model = $model;
        $this->data = $data;
    }

    /**
     * Retorna o nome do evento
     */
    abstract public function getName(): string;

    /**
     * Retorna o modelo associado ao evento
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Retorna os dados do evento
     */
    public function getData(): array
    {
        return $this->data;
    }
} 