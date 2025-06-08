<?php

namespace LadyPHP\Database\ORM;

use PDO;
use LadyPHP\Database\ORM\QueryBuilder;
use LadyPHP\Database\ORM\Traits\HasConnection;
use LadyPHP\Database\ORM\Concerns\HasEvents;
use LadyPHP\Database\ORM\Concerns\HasAttributes;
use LadyPHP\Database\ORM\Concerns\HasRelationships;
use LadyPHP\Database\ORM\Concerns\HasQueryBuilder;

abstract class Model
{
    use HasEvents;
    use HasConnection;
    use HasAttributes;
    use HasRelationships;
    use HasQueryBuilder;

    /**
     * Nome da tabela associada ao modelo
     */
    protected static string $table;

    /**
     * Chave primária do modelo
     */
    protected string $primaryKey = 'id';

    /**
     * Indica se o modelo deve usar timestamps
     */
    protected bool $timestamps = true;

    /**
     * Nome da coluna de created_at
     */
    protected string $createdAtColumn = 'created_at';

    /**
     * Nome da coluna de updated_at
     */
    protected string $updatedAtColumn = 'updated_at';

    protected array $attributes = [];
    protected array $original = [];
    protected bool $exists = false;
    protected static array $fillable = [];
    protected static array $guarded = ['id'];
    protected static array $with = [];
    protected static array $withCount = [];

    /**
     * Retorna o nome da tabela
     */
    public static function getTable(): string
    {
        if (!isset(static::$table)) {
            // Converte o nome da classe para snake_case e pluraliza
            $class = (new \ReflectionClass(static::class))->getShortName();
            static::$table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $class)) . 's';
        }
        return static::$table;
    }

    /**
     * Construtor do modelo
     */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * Preenche o modelo com atributos
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * Verifica se um atributo é preenchível
     */
    protected function isFillable(string $key): bool
    {
        if (in_array($key, static::$guarded)) {
            return false;
        }

        return empty(static::$fillable) || in_array($key, static::$fillable);
    }

    /**
     * Define um atributo
     */
    protected function setAttribute(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Cria um novo modelo e salva no banco
     */
    public static function create(array $attributes): static
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    /**
     * Retorna todos os registros
     */
    public static function all(): array
    {
        return static::query()->get();
    }

    /**
     * Cria uma nova query
     */
    public static function query(): QueryBuilder
    {
        return new QueryBuilder(static::class);
    }

    /**
     * Adiciona uma cláusula where
     */
    public static function where(string $column, $operator = null, $value = null): QueryBuilder
    {
        return static::query()->where($column, $operator, $value);
    }

    /**
     * Retorna o primeiro registro
     */
    public static function first(): ?static
    {
        return static::query()->first();
    }

    /**
     * Retorna o primeiro registro ou lança uma exceção
     */
    public static function firstOrFail(): static
    {
        $model = static::first();
        if (!$model) {
            throw new \Exception("Model not found");
        }
        return $model;
    }

    /**
     * Cria um novo modelo a partir de um array de atributos
     */
    public static function newFromBuilder(array $attributes): static
    {
        $model = new static;
        $model->exists = true;
        $model->attributes = $attributes;
        $model->original = $attributes;
        return $model;
    }

    /**
     * Salva o modelo
     */
    public function save(): bool
    {
        if ($this->exists) {
            return $this->update();
        }
        return $this->insert();
    }

    /**
     * Insere um novo registro
     */
    protected function insert(): bool
    {
        if (!$this->fireCreatingEvent()) {
            return false;
        }

        if ($this->timestamps) {
            $this->updateTimestamps();
        }

        $attributes = $this->getAttributes();
        $id = $this->getConnection()->table($this->getTable())->insertGetId($attributes);

        $this->setAttribute($this->getKeyName(), $id);
        $this->exists = true;

        $this->fireCreatedEvent();

        return true;
    }

    /**
     * Atualiza um registro existente
     */
    protected function update(): bool
    {
        if (!$this->fireUpdatingEvent()) {
            return false;
        }

        if ($this->timestamps) {
            $this->updateTimestamps();
        }

        $dirty = $this->getDirty();
        if (empty($dirty)) {
            return true;
        }

        $this->getConnection()->table($this->getTable())
            ->where($this->getKeyName(), $this->getKey())
            ->update($dirty);

        $this->syncOriginal();
        $this->fireUpdatedEvent();

        return true;
    }

    /**
     * Exclui um registro
     */
    public function delete(): bool
    {
        if (!$this->fireDeletingEvent()) {
            return false;
        }

        $this->getConnection()->table($this->getTable())
            ->where($this->getKeyName(), $this->getKey())
            ->delete();

        $this->exists = false;
        $this->fireDeletedEvent();

        return true;
    }

    /**
     * Retorna os atributos que foram modificados
     */
    protected function getDirty(): array
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
     * Define um atributo dinamicamente
     */
    public function __set(string $key, $value): void
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Obtém um atributo dinamicamente
     */
    public function __get(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Verifica se um atributo existe
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
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

    /**
     * Atualiza os timestamps do modelo
     */
    protected function updateTimestamps(): void
    {
        $time = $this->freshTimestamp();

        if (!$this->exists) {
            $this->setAttribute($this->createdAtColumn, $time);
        }

        $this->setAttribute($this->updatedAtColumn, $time);
    }

    /**
     * Retorna o timestamp atual
     */
    public function freshTimestamp(): string
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Retorna o nome da chave primária
     */
    public function getKeyName(): string
    {
        return $this->primaryKey;
    }

    /**
     * Retorna o valor da chave primária
     */
    public function getKey()
    {
        return $this->getAttribute($this->getKeyName());
    }

    /**
     * Retorna uma nova instância do modelo
     */
    public static function newModelInstance(array $attributes = []): static
    {
        return new static($attributes);
    }
} 