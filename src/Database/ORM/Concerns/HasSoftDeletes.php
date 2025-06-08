<?php

namespace LadyPHP\Database\ORM\Concerns;

trait HasSoftDeletes
{
    /**
     * Indica se o modelo usa exclusão suave
     */
    public bool $useSoftDeletes = true;

    /**
     * Nome da coluna de exclusão suave
     */
    public const DELETED_AT = 'deleted_at';

    /**
     * Retorna o nome da coluna de exclusão suave
     */
    public function getDeletedAtColumn(): string
    {
        return static::DELETED_AT;
    }

    /**
     * Retorna o timestamp de exclusão
     */
    public function getDeletedAt(): ?string
    {
        return $this->getAttribute(static::DELETED_AT);
    }

    /**
     * Verifica se o modelo foi excluído suavemente
     */
    public function isSoftDeleted(): bool
    {
        return !is_null($this->getDeletedAt());
    }

    /**
     * Restaura um modelo excluído suavemente
     */
    public function restore(): bool
    {
        if (!$this->isSoftDeleted()) {
            return false;
        }

        $this->setAttribute(static::DELETED_AT, null);

        return $this->save();
    }

    /**
     * Exclui permanentemente um modelo
     */
    public function forceDelete(): bool
    {
        $this->useSoftDeletes = false;

        return $this->delete();
    }

    /**
     * Exclui suavemente um modelo
     */
    public function softDelete(): bool
    {
        if ($this->isSoftDeleted()) {
            return false;
        }

        $this->setAttribute(static::DELETED_AT, $this->freshTimestamp());

        return $this->save();
    }

    /**
     * Restaura todos os modelos excluídos suavemente
     */
    public static function restoreAll(): int
    {
        return static::query()
            ->whereNotNull(static::DELETED_AT)
            ->update([static::DELETED_AT => null]);
    }

    /**
     * Exclui permanentemente todos os modelos excluídos suavemente
     */
    public static function forceDeleteAll(): int
    {
        return static::query()
            ->whereNotNull(static::DELETED_AT)
            ->delete();
    }

    /**
     * Exclui suavemente todos os modelos
     */
    public static function softDeleteAll(): int
    {
        return static::query()
            ->whereNull(static::DELETED_AT)
            ->update([static::DELETED_AT => date('Y-m-d H:i:s')]);
    }

    /**
     * Retorna apenas os modelos excluídos suavemente
     */
    public static function onlyTrashed(): \LadyPHP\Database\ORM\QueryBuilder
    {
        return static::query()->whereNotNull(static::DELETED_AT);
    }

    /**
     * Retorna apenas os modelos não excluídos suavemente
     */
    public static function withoutTrashed(): \LadyPHP\Database\ORM\QueryBuilder
    {
        return static::query()->whereNull(static::DELETED_AT);
    }

    /**
     * Retorna todos os modelos, incluindo os excluídos suavemente
     */
    public static function withTrashed(): \LadyPHP\Database\ORM\QueryBuilder
    {
        return static::query();
    }
} 