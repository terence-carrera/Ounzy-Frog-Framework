<?php

namespace Ounzy\FrogFramework\Core;

use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use Closure;

class Container
{
    protected array $bindings = [];
    protected array $singletons = [];

    public function bind(string $abstract, $concrete = null, bool $singleton = false): void
    {
        if ($concrete === null) {
            $concrete = $abstract; // auto-resolve
        }
        $this->bindings[$abstract] = compact('concrete', 'singleton');
    }

    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    public function instance(string $abstract, $object): void
    {
        $this->singletons[$abstract] = $object;
    }

    public function has(string $abstract): bool
    {
        return isset($this->singletons[$abstract]) || isset($this->bindings[$abstract]);
    }

    public function make(string $abstract)
    {
        if (isset($this->singletons[$abstract])) {
            return $this->singletons[$abstract];
        }
        if (!isset($this->bindings[$abstract])) {
            // Implicit binding
            $this->bind($abstract);
        }
        $definition = $this->bindings[$abstract];
        $concrete = $definition['concrete'];
        $object = $this->build($concrete);
        if ($definition['singleton']) {
            $this->singletons[$abstract] = $object;
        }
        return $object;
    }

    protected function build($concrete)
    {
        if ($concrete instanceof Closure) {
            return $concrete($this);
        }
        if (!class_exists($concrete)) {
            throw new \RuntimeException("Cannot resolve '{$concrete}' - class does not exist");
        }
        $ref = new ReflectionClass($concrete);
        if (!$ref->isInstantiable()) {
            throw new \RuntimeException("Class '{$concrete}' is not instantiable");
        }
        $ctor = $ref->getConstructor();
        if (!$ctor) {
            return new $concrete();
        }
        $deps = $this->resolveParameters($ctor->getParameters());
        return $ref->newInstanceArgs($deps);
    }

    public function call(callable|array $callable, array $provided = []): mixed
    {
        if ($callable === null) {
            throw new \RuntimeException('Attempted to call null as a callable (possible unresolved dependency or wrong handler signature).');
        }
        if (is_array($callable)) {
            [$class, $method] = $callable;
            $obj = is_string($class) ? $this->make($class) : $class;
            $refMethod = new ReflectionMethod($obj, $method);
            $args = $this->resolveParameters($refMethod->getParameters(), $provided);
            return $refMethod->invokeArgs($obj, $args);
        }
        if ($callable instanceof Closure) {
            $ref = new ReflectionFunction($callable);
            $args = $this->resolveParameters($ref->getParameters(), $provided);
            return $callable(...$args);
        }
        // plain callable (function name)
        $ref = new ReflectionFunction($callable);
        $args = $this->resolveParameters($ref->getParameters(), $provided);
        return $callable(...$args);
    }

    protected function resolveParameters(array $parameters, array $provided = []): array
    {
        $resolved = [];
        foreach ($parameters as $param) {
            $name = $param->getName();
            $type = $param->getType();
            if (array_key_exists($name, $provided)) {
                $resolved[] = $provided[$name];
                continue;
            }
            if ($type && !$type->isBuiltin()) {
                $resolved[] = $this->make($type->getName());
                continue;
            }
            if ($param->isDefaultValueAvailable()) {
                $resolved[] = $param->getDefaultValue();
                continue;
            }
            // fallback null
            $resolved[] = null;
        }
        return $resolved;
    }
}
