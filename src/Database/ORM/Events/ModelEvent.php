<?php

namespace LadyPHP\Database\ORM\Events;

use LadyPHP\Database\ORM\Model;

class ModelEvent extends Event
{
    protected array $attributes;
    protected array $original;

    public function __construct(Model $model, array $attributes = [], array $original = [])
    {
        parent::__construct($model);
        $this->attributes = $attributes;
        $this->original = $original;
    }

    /**
     * Retorna os atributos do modelo
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Retorna os atributos originais do modelo
     */
    public function getOriginal(): array
    {
        return $this->original;
    }

    /**
     * Retorna os dados do evento
     */
    public function getData(): array
    {
        return [
            'attributes' => $this->attributes,
            'original' => $this->original,
        ];
    }
} 