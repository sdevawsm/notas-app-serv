<?php

namespace LadyPHP\Database\ORM\Concerns;

trait HasUuid
{
    /**
     * Indica se o modelo usa UUID
     */
    public bool $useUuid = true;

    /**
     * Nome da coluna de UUID
     */
    public const UUID = 'uuid';

    /**
     * Retorna o nome da coluna de UUID
     */
    public function getUuidColumn(): string
    {
        return static::UUID;
    }

    /**
     * Retorna o UUID do modelo
     */
    public function getUuid(): ?string
    {
        return $this->getAttribute(static::UUID);
    }

    /**
     * Define o UUID do modelo
     */
    public function setUuid(string $uuid): void
    {
        $this->setAttribute(static::UUID, $uuid);
    }

    /**
     * Gera um novo UUID
     */
    public function generateUuid(): string
    {
        return (string) \Ramsey\Uuid\Uuid::uuid4();
    }

    /**
     * Verifica se o modelo tem UUID
     */
    public function hasUuid(): bool
    {
        return !is_null($this->getUuid());
    }

    /**
     * Retorna o modelo pelo UUID
     */
    public static function findByUuid(string $uuid): ?static
    {
        return static::query()->where(static::UUID, $uuid)->first();
    }

    /**
     * Retorna o modelo pelo UUID ou lança uma exceção
     */
    public static function findByUuidOrFail(string $uuid): static
    {
        $model = static::findByUuid($uuid);

        if (is_null($model)) {
            throw new \LadyPHP\Database\ORM\Exceptions\ModelNotFoundException();
        }

        return $model;
    }

    /**
     * Boot do trait
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if ($model->useUuid && !$model->hasUuid()) {
                $model->setUuid($model->generateUuid());
            }
        });
    }
} 