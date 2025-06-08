<?php

namespace LadyPHP\Database\ORM;

use PDO;
use App\Models\Base\Model;

class QueryBuilder
{
    protected string $model;
    protected array $wheres = [];
    protected array $orders = [];
    protected ?int $limit = null;
    protected ?int $offset = null;
    protected array $selects = ['*'];
    protected array $joins = [];
    protected array $with = [];
    protected array $withCount = [];

    public function __construct(string $model)
    {
        $this->model = $model;
    }

    /**
     * Adiciona uma cláusula where
     */
    public function where(string $column, $operator = null, $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $validOperators = ['=', '>', '<', '>=', '<=', '<>', '!=', 'LIKE', 'IN'];
        if (!in_array(strtoupper($operator), $validOperators)) {
            throw new \InvalidArgumentException("Invalid operator: {$operator}");
        }

        $this->wheres[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'and'
        ];

        return $this;
    }

    /**
     * Adiciona uma cláusula where com OR
     */
    public function orWhere(string $column, $operator = null, $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'or'
        ];

        return $this;
    }

    /**
     * Adiciona uma cláusula where com IN
     */
    public function whereIn(string $column, array $values): self
    {
        $this->wheres[] = [
            'column' => $column,
            'operator' => 'IN',
            'value' => $values,
            'boolean' => 'and'
        ];

        return $this;
    }

    /**
     * Adiciona uma cláusula where com LIKE
     */
    public function whereLike(string $column, string $value): self
    {
        return $this->where($column, 'LIKE', "%{$value}%");
    }

    /**
     * Adiciona uma cláusula order by
     */
    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->orders[] = [
            'column' => $column,
            'direction' => strtoupper($direction)
        ];

        return $this;
    }

    /**
     * Define o limite de registros
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Define o offset
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Define as colunas a serem selecionadas
     */
    public function select(array $columns): self
    {
        $this->selects = $columns;
        return $this;
    }

    /**
     * Adiciona um join
     */
    public function join(string $table, string $first, string $operator, string $second, string $type = 'inner'): self
    {
        $this->joins[] = [
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second,
            'type' => $type
        ];

        return $this;
    }

    /**
     * Adiciona um left join
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'left');
    }

    /**
     * Adiciona um right join
     */
    public function rightJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'right');
    }

    /**
     * Carrega relacionamentos
     */
    public function with(array $relations): self
    {
        $this->with = $relations;
        return $this;
    }

    /**
     * Carrega contagem de relacionamentos
     */
    public function withCount(array $relations): self
    {
        $this->withCount = $relations;
        return $this;
    }

    /**
     * Retorna o primeiro registro
     */
    public function first(): ?object
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    /**
     * Retorna todos os registros
     */
    public function get(): array
    {
        $sql = $this->toSql();
        $bindings = $this->getBindings();
        
        $stmt = $this->model::getConnection()->prepare($sql);
        $stmt->execute($bindings);
        
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $model = new $this->model;
            $results[] = $model->newFromBuilder($row);
        }

        if (!empty($this->with)) {
            $this->eagerLoadRelations($results);
        }

        if (!empty($this->withCount)) {
            $this->loadCount($results);
        }

        return $results;
    }

    /**
     * Retorna o número de registros
     */
    public function count(): int
    {
        $this->selects = ['COUNT(*) as count'];
        $sql = $this->toSql();
        $stmt = $this->model::getConnection()->prepare($sql);
        $stmt->execute($this->getBindings());
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    /**
     * Gera a SQL da query
     */
    protected function toSql(): string
    {
        $sql = "SELECT " . implode(', ', $this->selects) . " FROM " . $this->model::getTable();

        if (!empty($this->joins)) {
            foreach ($this->joins as $join) {
                $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
            }
        }

        if (!empty($this->wheres)) {
            $sql .= " WHERE ";
            $conditions = [];
            
            foreach ($this->wheres as $where) {
                $condition = "{$where['column']} {$where['operator']} ?";
                if (!empty($conditions)) {
                    $condition = "{$where['boolean']} " . $condition;
                }
                $conditions[] = $condition;
            }
            
            $sql .= implode(' ', $conditions);
        }

        if (!empty($this->orders)) {
            $sql .= " ORDER BY ";
            $orders = [];
            
            foreach ($this->orders as $order) {
                $orders[] = "{$order['column']} {$order['direction']}";
            }
            
            $sql .= implode(', ', $orders);
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT " . $this->limit;
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET " . $this->offset;
        }

        return $sql;
    }

    /**
     * Retorna os bindings da query
     */
    protected function getBindings(): array
    {
        $bindings = [];
        
        foreach ($this->wheres as $where) {
            if (is_array($where['value'])) {
                $bindings = array_merge($bindings, $where['value']);
            } else {
                $bindings[] = $where['value'];
            }
        }
        
        return $bindings;
    }

    /**
     * Carrega relacionamentos eager
     */
    protected function eagerLoadRelations(array $models): void
    {
        // Implementação do eager loading
    }

    /**
     * Carrega contagem de relacionamentos
     */
    protected function loadCount(array $models): void
    {
        // Implementação do withCount
    }
} 