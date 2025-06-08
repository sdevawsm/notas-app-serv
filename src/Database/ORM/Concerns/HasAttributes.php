<?php

namespace LadyPHP\Database\ORM\Concerns;

trait HasAttributes
{
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
     * Retorna os atributos que foram modificados
     */
    public function getDirty(): array
    {
        $dirty = [];

        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $value !== $this->original[$key]) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    /**
     * Define um atributo no modelo
     */
    public function setAttribute(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Retorna um atributo do modelo
     */
    public function getAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Verifica se um atributo existe no modelo
     */
    public function hasAttribute(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Sincroniza os atributos originais com os atuais
     */
    public function syncOriginal(): void
    {
        $this->original = $this->attributes;
    }

    /**
     * Verifica se um atributo é preenchível
     */
    protected function isFillable(string $key): bool
    {
        if (in_array($key, static::$fillable)) {
            return true;
        }

        if (in_array($key, static::$guarded)) {
            return false;
        }

        return empty(static::$fillable) && !in_array($key, static::$guarded);
    }

    /**
     * Converte o modelo para array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Converte o modelo para JSON
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
} 