<?php

namespace LadyPHP\Database\ORM\Concerns;

trait HasTimestamps
{
    /**
     * Indica se o modelo usa timestamps
     */
    public bool $timestamps = true;

    /**
     * Nome da coluna de criação
     */
    public const CREATED_AT = 'created_at';

    /**
     * Nome da coluna de atualização
     */
    public const UPDATED_AT = 'updated_at';

    /**
     * Retorna os nomes das colunas de timestamp
     */
    public function getTimestampColumns(): array
    {
        return [
            static::CREATED_AT,
            static::UPDATED_AT,
        ];
    }

    /**
     * Retorna o nome da coluna de criação
     */
    public function getCreatedAtColumn(): string
    {
        return static::CREATED_AT;
    }

    /**
     * Retorna o nome da coluna de atualização
     */
    public function getUpdatedAtColumn(): string
    {
        return static::UPDATED_AT;
    }

    /**
     * Atualiza os timestamps do modelo
     */
    public function updateTimestamps(): void
    {
        $time = $this->freshTimestamp();

        if (!$this->isDirty(static::UPDATED_AT)) {
            $this->setAttribute(static::UPDATED_AT, $time);
        }

        if (!$this->exists && !$this->isDirty(static::CREATED_AT)) {
            $this->setAttribute(static::CREATED_AT, $time);
        }
    }

    /**
     * Retorna o timestamp atual
     */
    public function freshTimestamp(): string
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Retorna o timestamp de criação
     */
    public function getCreatedAt(): ?string
    {
        return $this->getAttribute(static::CREATED_AT);
    }

    /**
     * Retorna o timestamp de atualização
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getAttribute(static::UPDATED_AT);
    }
} 