<?php

namespace LadyPHP\Database\ORM\Concerns;

use LadyPHP\Database\ORM\Relations\HasOne;
use LadyPHP\Database\ORM\Relations\HasMany;
use LadyPHP\Database\ORM\Relations\BelongsTo;
use LadyPHP\Database\ORM\Relations\BelongsToMany;
use LadyPHP\Database\ORM\Relations\MorphTo;
use LadyPHP\Database\ORM\Relations\MorphOne;
use LadyPHP\Database\ORM\Relations\MorphMany;
use LadyPHP\Database\ORM\Relations\MorphToMany;
use LadyPHP\Database\ORM\Relations\MorphByMany;
use LadyPHP\Database\ORM\Relations\HasManyThrough;
use LadyPHP\Database\ORM\Relations\HasOneThrough;

trait HasRelationships
{
    /**
     * Define um relacionamento um-para-um
     */
    public function hasOne(string $related, string $foreignKey = null, string $localKey = null): HasOne
    {
        $instance = $this->newRelatedInstance($related);
        $foreignKey = $foreignKey ?? $this->getForeignKey();
        $localKey = $localKey ?? $this->getKeyName();

        return new HasOne($instance->newQuery(), $this, $instance->getTable() . '.' . $foreignKey, $localKey);
    }

    /**
     * Define um relacionamento um-para-muitos
     */
    public function hasMany(string $related, string $foreignKey = null, string $localKey = null): HasMany
    {
        $instance = $this->newRelatedInstance($related);
        $foreignKey = $foreignKey ?? $this->getForeignKey();
        $localKey = $localKey ?? $this->getKeyName();

        return new HasMany($instance->newQuery(), $this, $instance->getTable() . '.' . $foreignKey, $localKey);
    }

    /**
     * Define um relacionamento pertence-a
     */
    public function belongsTo(string $related, string $foreignKey = null, string $ownerKey = null, string $relation = null): BelongsTo
    {
        $instance = $this->newRelatedInstance($related);
        $foreignKey = $foreignKey ?? $instance->getForeignKey();
        $ownerKey = $ownerKey ?? $instance->getKeyName();

        return new BelongsTo($instance->newQuery(), $this, $foreignKey, $ownerKey, $relation);
    }

    /**
     * Define um relacionamento muitos-para-muitos
     */
    public function belongsToMany(string $related, string $table = null, string $foreignPivotKey = null, string $relatedPivotKey = null, string $parentKey = null, string $relatedKey = null): BelongsToMany
    {
        $instance = $this->newRelatedInstance($related);
        $foreignPivotKey = $foreignPivotKey ?? $this->getForeignKey();
        $relatedPivotKey = $relatedPivotKey ?? $instance->getForeignKey();
        $table = $table ?? $this->joiningTable($related);

        return new BelongsToMany(
            $instance->newQuery(),
            $this,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey ?? $this->getKeyName(),
            $relatedKey ?? $instance->getKeyName()
        );
    }

    /**
     * Define um relacionamento polimórfico
     */
    public function morphTo(string $name = null, string $type = null, string $id = null): MorphTo
    {
        $name = $name ?? $this->guessBelongsToRelation();
        $type = $type ?? $name . '_type';
        $id = $id ?? $name . '_id';

        return new MorphTo(
            $this->newQuery(),
            $this,
            $id,
            $type,
            $name
        );
    }

    /**
     * Define um relacionamento polimórfico um-para-um
     */
    public function morphOne(string $related, string $name, string $type = null, string $id = null): MorphOne
    {
        $instance = $this->newRelatedInstance($related);
        $type = $type ?? $name . '_type';
        $id = $id ?? $name . '_id';

        return new MorphOne($instance->newQuery(), $this, $type, $id);
    }

    /**
     * Define um relacionamento polimórfico um-para-muitos
     */
    public function morphMany(string $related, string $name, string $type = null, string $id = null): MorphMany
    {
        $instance = $this->newRelatedInstance($related);
        $type = $type ?? $name . '_type';
        $id = $id ?? $name . '_id';

        return new MorphMany($instance->newQuery(), $this, $type, $id);
    }

    /**
     * Define um relacionamento polimórfico muitos-para-muitos
     */
    public function morphToMany(string $related, string $name, string $table = null, string $foreignPivotKey = null, string $relatedPivotKey = null, string $parentKey = null, string $relatedKey = null): MorphToMany
    {
        $instance = $this->newRelatedInstance($related);
        $foreignPivotKey = $foreignPivotKey ?? $name . '_id';
        $relatedPivotKey = $relatedPivotKey ?? $instance->getForeignKey();
        $table = $table ?? $this->joiningTable($related, $name);

        return new MorphToMany(
            $instance->newQuery(),
            $this,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey ?? $this->getKeyName(),
            $relatedKey ?? $instance->getKeyName(),
            $name
        );
    }

    /**
     * Define um relacionamento polimórfico muitos-para-muitos inverso
     */
    public function morphedByMany(string $related, string $name, string $table = null, string $foreignPivotKey = null, string $relatedPivotKey = null, string $parentKey = null, string $relatedKey = null): MorphByMany
    {
        $instance = $this->newRelatedInstance($related);
        $foreignPivotKey = $foreignPivotKey ?? $instance->getForeignKey();
        $relatedPivotKey = $relatedPivotKey ?? $name . '_id';
        $table = $table ?? $this->joiningTable($related, $name);

        return new MorphByMany(
            $instance->newQuery(),
            $this,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey ?? $this->getKeyName(),
            $relatedKey ?? $instance->getKeyName(),
            $name
        );
    }

    /**
     * Define um relacionamento através de um modelo intermediário
     */
    public function hasManyThrough(string $related, string $through, string $firstKey = null, string $secondKey = null, string $localKey = null, string $secondLocalKey = null): HasManyThrough
    {
        $through = new $through;
        $firstKey = $firstKey ?? $this->getForeignKey();
        $secondKey = $secondKey ?? $through->getForeignKey();
        $localKey = $localKey ?? $this->getKeyName();
        $secondLocalKey = $secondLocalKey ?? $through->getKeyName();

        return new HasManyThrough(
            $this->newRelatedInstance($related)->newQuery(),
            $this,
            $through,
            $firstKey,
            $secondKey,
            $localKey,
            $secondLocalKey
        );
    }

    /**
     * Define um relacionamento um-para-um através de um modelo intermediário
     */
    public function hasOneThrough(string $related, string $through, string $firstKey = null, string $secondKey = null, string $localKey = null, string $secondLocalKey = null): HasOneThrough
    {
        $through = new $through;
        $firstKey = $firstKey ?? $this->getForeignKey();
        $secondKey = $secondKey ?? $through->getForeignKey();
        $localKey = $localKey ?? $this->getKeyName();
        $secondLocalKey = $secondLocalKey ?? $through->getKeyName();

        return new HasOneThrough(
            $this->newRelatedInstance($related)->newQuery(),
            $this,
            $through,
            $firstKey,
            $secondKey,
            $localKey,
            $secondLocalKey
        );
    }

    /**
     * Retorna uma nova instância do modelo relacionado
     */
    protected function newRelatedInstance(string $class): static
    {
        return new $class;
    }

    /**
     * Retorna o nome da chave estrangeira
     */
    public function getForeignKey(): string
    {
        return strtolower(class_basename($this)) . '_id';
    }

    /**
     * Retorna o nome da tabela de junção
     */
    public function joiningTable(string $related, string $name = null): string
    {
        $models = [
            strtolower(class_basename($this)),
            strtolower(class_basename($related)),
        ];

        sort($models);

        return implode('_', $models);
    }

    /**
     * Tenta adivinhar o nome do relacionamento
     */
    protected function guessBelongsToRelation(): string
    {
        $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2];

        return $caller['function'];
    }
} 