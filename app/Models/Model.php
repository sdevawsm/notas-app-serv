<?php

namespace App\Models;

use LadyPHP\Database\Connection\ConnectionManager;
use PDO;

abstract class Model
{
    protected PDO $connection;
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $attributes = [];
    protected bool $timestamps = true; 

    public function __construct(array $attributes = [])
    {
        $this->connection = ConnectionManager::getInstance()->getPdo();
        $this->fill($attributes);
    }

    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, $value): void
    {
        if (in_array($name, $this->fillable)) {
            $this->attributes[$name] = $value;
        }
    }

    /**
     * Converte o modelo para uma string JSON
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Converte uma coleção de modelos para JSON
     */
    public static function collectionToJson(array $models): string
    {
        $data = array_map(fn($model) => $model->toArray(), $models);
        return json_encode($data);
    }

    /**
     * Converte o modelo para uma string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Converte o modelo para um array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    protected function fill(array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }
    }

    public function save(): bool
    {
        try {
            if (empty($this->attributes[$this->primaryKey])) {
                return $this->insert();
            }
            return $this->update();
        } catch (\PDOException $e) {
            throw new \Exception("Erro ao salvar registro: " . $e->getMessage());
        }
    }

    protected function insert(): bool
    {
        $fields = array_keys($this->attributes);
        $placeholders = array_map(fn($field) => ":$field", $fields);
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->execute($this->attributes);
        
        if ($result) {
            $this->attributes[$this->primaryKey] = (int) $this->connection->lastInsertId();
        }
        
        return $result;
    }

    protected function update(): bool
    {
        $fields = array_filter(
            array_keys($this->attributes),
            fn($field) => $field !== $this->primaryKey
        );
        
        $set = array_map(
            fn($field) => "$field = :$field",
            $fields
        );
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . 
               " WHERE {$this->primaryKey} = :{$this->primaryKey}";
        
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($this->attributes);
    }

    public static function find(int $id): ?static
    {
        $instance = new static();
        $sql = "SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = :id";
        $stmt = $instance->connection->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        if ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return new static($data);
        }
        
        return null;
    }

    public static function all(): array
    {
        $instance = new static();
        $sql = "SELECT * FROM {$instance->table}";
        $stmt = $instance->connection->prepare($sql);
        $stmt->execute();
        
        $results = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = new static($data);
        }
        
        return $results;
    }

    public static function where(string $column, string $operator, $value): array
    {
        $instance = new static();
        $sql = "SELECT * FROM {$instance->table} WHERE $column $operator :value";
        $stmt = $instance->connection->prepare($sql);
        $stmt->execute([':value' => $value]);
        
        $results = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = new static($data);
        }
        
        return $results;
    }

    public function delete(): bool
    {
        if (empty($this->attributes[$this->primaryKey])) {
            return false;
        }

        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([':id' => $this->attributes[$this->primaryKey]]);
    }
} 