<?php

namespace LadyPHP\Database\ORM\Concerns;

trait HasCache
{
    /**
     * Indica se o modelo usa cache
     */
    public bool $useCache = true;

    /**
     * O tempo de expiração do cache em segundos
     */
    public int $cacheExpiration = 3600;

    /**
     * O prefixo da chave do cache
     */
    public string $cachePrefix = 'model:';

    /**
     * Retorna a chave do cache
     */
    public function getCacheKey(): string
    {
        return $this->cachePrefix . $this->getTable() . ':' . $this->getKey();
    }

    /**
     * Retorna o tempo de expiração do cache
     */
    public function getCacheExpiration(): int
    {
        return $this->cacheExpiration;
    }

    /**
     * Define o tempo de expiração do cache
     */
    public function setCacheExpiration(int $seconds): void
    {
        $this->cacheExpiration = $seconds;
    }

    /**
     * Retorna o prefixo da chave do cache
     */
    public function getCachePrefix(): string
    {
        return $this->cachePrefix;
    }

    /**
     * Define o prefixo da chave do cache
     */
    public function setCachePrefix(string $prefix): void
    {
        $this->cachePrefix = $prefix;
    }

    /**
     * Retorna o cache do modelo
     */
    public function getCache(): mixed
    {
        if (!$this->useCache) {
            return null;
        }

        return \LadyPHP\Cache\Cache::get($this->getCacheKey());
    }

    /**
     * Define o cache do modelo
     */
    public function setCache(mixed $value): void
    {
        if (!$this->useCache) {
            return;
        }

        \LadyPHP\Cache\Cache::set($this->getCacheKey(), $value, $this->getCacheExpiration());
    }

    /**
     * Remove o cache do modelo
     */
    public function removeCache(): void
    {
        if (!$this->useCache) {
            return;
        }

        \LadyPHP\Cache\Cache::delete($this->getCacheKey());
    }

    /**
     * Limpa o cache do modelo
     */
    public function clearCache(): void
    {
        if (!$this->useCache) {
            return;
        }

        \LadyPHP\Cache\Cache::delete($this->getCacheKey());
    }

    /**
     * Verifica se o modelo tem cache
     */
    public function hasCache(): bool
    {
        if (!$this->useCache) {
            return false;
        }

        return \LadyPHP\Cache\Cache::has($this->getCacheKey());
    }

    /**
     * Retorna o cache do modelo ou executa o callback
     */
    public function remember(mixed $callback): mixed
    {
        if (!$this->useCache) {
            return $callback();
        }

        return \LadyPHP\Cache\Cache::remember($this->getCacheKey(), $this->getCacheExpiration(), $callback);
    }

    /**
     * Retorna o cache do modelo ou executa o callback e armazena o resultado
     */
    public function rememberForever(mixed $callback): mixed
    {
        if (!$this->useCache) {
            return $callback();
        }

        return \LadyPHP\Cache\Cache::rememberForever($this->getCacheKey(), $callback);
    }
} 