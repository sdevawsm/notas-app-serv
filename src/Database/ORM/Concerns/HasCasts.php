<?php

namespace LadyPHP\Database\ORM\Concerns;

trait HasCasts
{
    /**
     * Os atributos que devem ser convertidos
     */
    protected array $casts = [];

    /**
     * Os atributos que devem ser convertidos para datas
     */
    protected array $dates = [];

    /**
     * Os atributos que devem ser convertidos para arrays
     */
    protected array $arrayable = [];

    /**
     * Os atributos que devem ser convertidos para JSON
     */
    protected array $jsonable = [];

    /**
     * Os atributos que devem ser convertidos para booleanos
     */
    protected array $booleanable = [];

    /**
     * Os atributos que devem ser convertidos para inteiros
     */
    protected array $integerable = [];

    /**
     * Os atributos que devem ser convertidos para floats
     */
    protected array $floatable = [];

    /**
     * Os atributos que devem ser convertidos para strings
     */
    protected array $stringable = [];

    /**
     * Converte um atributo para o tipo especificado
     */
    protected function castAttribute(string $key, mixed $value): mixed
    {
        if (is_null($value)) {
            return $value;
        }

        $type = $this->getCastType($key);

        switch ($type) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'real':
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'object':
                return json_decode(json_encode($value));
            case 'array':
            case 'json':
                return json_decode(json_encode($value), true);
            case 'date':
                return $this->asDate($value);
            case 'datetime':
                return $this->asDateTime($value);
            case 'timestamp':
                return $this->asTimestamp($value);
            default:
                return $value;
        }
    }

    /**
     * Retorna o tipo de cast para um atributo
     */
    protected function getCastType(string $key): string
    {
        if (isset($this->casts[$key])) {
            return $this->casts[$key];
        }

        if (in_array($key, $this->dates)) {
            return 'datetime';
        }

        if (in_array($key, $this->arrayable)) {
            return 'array';
        }

        if (in_array($key, $this->jsonable)) {
            return 'json';
        }

        if (in_array($key, $this->booleanable)) {
            return 'boolean';
        }

        if (in_array($key, $this->integerable)) {
            return 'integer';
        }

        if (in_array($key, $this->floatable)) {
            return 'float';
        }

        if (in_array($key, $this->stringable)) {
            return 'string';
        }

        return 'string';
    }

    /**
     * Converte um valor para data
     */
    protected function asDate(mixed $value): string
    {
        return date('Y-m-d', strtotime($value));
    }

    /**
     * Converte um valor para data e hora
     */
    protected function asDateTime(mixed $value): string
    {
        return date('Y-m-d H:i:s', strtotime($value));
    }

    /**
     * Converte um valor para timestamp
     */
    protected function asTimestamp(mixed $value): int
    {
        return strtotime($value);
    }

    /**
     * Retorna os atributos que devem ser convertidos
     */
    public function getCasts(): array
    {
        return $this->casts;
    }

    /**
     * Retorna os atributos que devem ser convertidos para datas
     */
    public function getDates(): array
    {
        return $this->dates;
    }

    /**
     * Retorna os atributos que devem ser convertidos para arrays
     */
    public function getArrayable(): array
    {
        return $this->arrayable;
    }

    /**
     * Retorna os atributos que devem ser convertidos para JSON
     */
    public function getJsonable(): array
    {
        return $this->jsonable;
    }

    /**
     * Retorna os atributos que devem ser convertidos para booleanos
     */
    public function getBooleanable(): array
    {
        return $this->booleanable;
    }

    /**
     * Retorna os atributos que devem ser convertidos para inteiros
     */
    public function getIntegerable(): array
    {
        return $this->integerable;
    }

    /**
     * Retorna os atributos que devem ser convertidos para floats
     */
    public function getFloatable(): array
    {
        return $this->floatable;
    }

    /**
     * Retorna os atributos que devem ser convertidos para strings
     */
    public function getStringable(): array
    {
        return $this->stringable;
    }
} 