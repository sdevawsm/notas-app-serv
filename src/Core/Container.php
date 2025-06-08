<?php

namespace LadyPHP\Core;

class Container {
    /**
     * Os bindings registrados no container
     *
     * @var array
     */
    protected $bindings = [];

    /**
     * As instâncias compartilhadas registradas no container
     *
     * @var array
     */
    protected $instances = [];

    /**
     * Registra um binding no container
     *
     * @param string $abstract
     * @param \Closure|string|null $concrete
     * @param bool $shared
     * @return void
     */
    public function bind($abstract, $concrete = null, $shared = false)
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'shared' => $shared,
        ];
    }

    /**
     * Registra um binding compartilhado no container
     *
     * @param string $abstract
     * @param \Closure|string|null $concrete
     * @return void
     */
    public function singleton($abstract, $concrete = null)
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Resolve o tipo dado do container
     *
     * @param string $abstract
     * @return mixed
     *
     * @throws \Exception
     */
    public function make($abstract)
    {
        // Se já existe uma instância compartilhada, retorna ela
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Se não existe binding, tenta criar uma instância
        if (!isset($this->bindings[$abstract])) {
            return $this->build($abstract);
        }

        $concrete = $this->bindings[$abstract]['concrete'];
        $shared = $this->bindings[$abstract]['shared'];

        // Se o concrete é uma closure, executa ela
        if ($concrete instanceof \Closure) {
            $object = $concrete($this);
        } else {
            $object = $this->build($concrete);
        }

        // Se for compartilhado, armazena a instância
        if ($shared) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * Verifica se existe um binding para o tipo dado
     *
     * @param string $abstract
     * @return bool
     */
    public function has($abstract)
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }

    /**
     * Constrói uma instância do tipo dado
     *
     * @param string $concrete
     * @return mixed
     *
     * @throws \Exception
     */
    protected function build($concrete)
    {
        if (!class_exists($concrete)) {
            throw new \Exception("Class {$concrete} does not exist.");
        }

        $reflector = new \ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new \Exception("Target [$concrete] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $concrete;
        }

        $dependencies = $constructor->getParameters();
        $instances = $this->resolveDependencies($dependencies);

        return $reflector->newInstanceArgs($instances);
    }

    /**
     * Resolve todas as dependências do construtor
     *
     * @param array $dependencies
     * @return array
     */
    protected function resolveDependencies(array $dependencies)
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            $results[] = $this->resolveDependency($dependency);
        }

        return $results;
    }

    /**
     * Resolve uma dependência do construtor
     *
     * @param \ReflectionParameter $parameter
     * @return mixed
     *
     * @throws \Exception
     */
    protected function resolveDependency($parameter)
    {
        $class = $parameter->getClass();

        if ($class) {
            return $this->make($class->name);
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new \Exception("Unresolvable dependency resolving [$parameter]");
    }

    /**
     * Registra uma instância compartilhada no container
     *
     * @param string $abstract
     * @param mixed $instance
     * @return void
     */
    public function instance($abstract, $instance)
    {
        $this->instances[$abstract] = $instance;
    }
}
