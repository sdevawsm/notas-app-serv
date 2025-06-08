<?php

namespace LadyPHP\Database\ORM\Events;

use LadyPHP\Database\ORM\Model;

class EventDispatcher
{
    protected static array $listeners = [];
    protected static array $modelListeners = [];

    /**
     * Registra um listener para um evento
     */
    public static function listen(string $event, callable $listener): void
    {
        if (!isset(self::$listeners[$event])) {
            self::$listeners[$event] = [];
        }

        self::$listeners[$event][] = $listener;
    }

    /**
     * Registra um listener para um evento específico de um modelo
     */
    public static function listenModel(string $model, string $event, callable $listener): void
    {
        if (!isset(self::$modelListeners[$model])) {
            self::$modelListeners[$model] = [];
        }

        if (!isset(self::$modelListeners[$model][$event])) {
            self::$modelListeners[$model][$event] = [];
        }

        self::$modelListeners[$model][$event][] = $listener;
    }

    /**
     * Remove um listener de um evento
     */
    public static function forget(string $event): void
    {
        unset(self::$listeners[$event]);
    }

    /**
     * Remove um listener de um evento específico de um modelo
     */
    public static function forgetModel(string $model, string $event): void
    {
        unset(self::$modelListeners[$model][$event]);
    }

    /**
     * Remove todos os listeners
     */
    public static function flush(): void
    {
        self::$listeners = [];
        self::$modelListeners = [];
    }

    /**
     * Dispara um evento
     */
    public static function dispatch(EventInterface $event): void
    {
        $eventName = $event->getName();
        $model = $event->getModel();
        $modelClass = get_class($model);

        // Dispara listeners globais
        if (isset(self::$listeners[$eventName])) {
            foreach (self::$listeners[$eventName] as $listener) {
                $listener($event);
            }
        }

        // Dispara listeners específicos do modelo
        if (isset(self::$modelListeners[$modelClass][$eventName])) {
            foreach (self::$modelListeners[$modelClass][$eventName] as $listener) {
                $listener($event);
            }
        }
    }

    /**
     * Verifica se um evento tem listeners
     */
    public static function hasListeners(string $event): bool
    {
        return isset(self::$listeners[$event]) && !empty(self::$listeners[$event]);
    }

    /**
     * Verifica se um evento específico de um modelo tem listeners
     */
    public static function hasModelListeners(string $model, string $event): bool
    {
        return isset(self::$modelListeners[$model][$event]) && !empty(self::$modelListeners[$model][$event]);
    }
} 