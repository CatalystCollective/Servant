<?php
/**
 * This file is part of the Catalyst Servant.
 *
 * (c)2017 Matthias Kaschubowski and the Catalyst Collective
 *
 * This code is licensed under the MIT license,
 * a copy of the license is stored at the project root.
 */

namespace Catalyst\Servant;


use Catalyst\Servant\Exceptions\ServantException;
use Catalyst\Servant\Exceptions\UnresolvedDependencyException;
use ReflectionParameter;
use ReflectionFunction;

class RepositoryServant extends ServantAbstract implements ServantInterface, RepositoryInterface
{
    protected $interfaces = [];
    protected $aliases = [];

    /**
     * sets a concrete for the given alias.
     *
     * @param string $alias
     * @param string|object|\Closure $concrete
     * @return void
     */
    public function set(string $alias, $concrete)
    {
        $alias = $this->marshalKey($alias);

        if ( $concrete instanceof \Closure ) {
            $this->aliases[$alias] = $concrete;

            return;
        }

        if ( is_string($concrete) ) {
            $this->aliases[$alias] = function() use($concrete) {
                return new $concrete;
            };

            return;
        }

        if ( is_object($concrete) ) {
            $this->aliases[$alias] = function() use($concrete) {
                return $concrete;
            };

            return;
        }

        throw new ServantException('Unknown concrete type: '.gettype($concrete));
    }

    /**
     * checks whether a given alias is stored or not.
     *
     * @param string[] ...$alias
     * @return bool
     */
    public function has(string ... $alias): bool
    {
        foreach ( $alias as $current ) {
            if ( ! array_key_exists($this->marshalKey($current), $this->aliases) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * sets a concrete for the given interface.
     *
     * @param string $interface
     * @param $concrete
     * @return mixed
     */
    public function force(string $interface, $concrete)
    {
        $interface = $this->marshalKey($interface);

        if ( $concrete instanceof \Closure ) {
            $this->interfaces[$interface] = $concrete;

            return;
        }

        if ( is_string($concrete) ) {
            $this->interfaces[$interface] = function() use($concrete) {
                return new $concrete;
            };

            return;
        }

        if ( is_object($concrete) ) {
            $this->interfaces[$interface] = function() use($concrete) {
                return $concrete;
            };

            return;
        }

        throw new ServantException('Unknown concrete type: '.gettype($concrete));
    }

    /**
     * checks whether the provided interfaces are known or not.
     *
     * @param \string[] ...$interfaces
     * @return bool
     */
    public function ensure(string ... $interfaces): bool
    {
        foreach ( $interfaces as $current ) {
            if ( ! array_key_exists($this->marshalKey($current), $this->interfaces) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * sets the given classes as its own interface bindings.
     *
     * @param \string[] ...$classes
     * @return void
     */
    public function acknowledge(string ... $classes)
    {
        foreach ( $classes as $current ) {
            $this->force($current, $classes);
        }
    }

    /**
     * Resolves the given class and optional alias.
     *
     * @param string $class
     * @param string|null $alias
     * @throws UnresolvedDependencyException when the dependency can not be resolved
     * @return object
     */
    public function resolve(string $class, string $alias = null)
    {
        $className = $this->marshalKey($class);
        $aliasName = $alias !== null ? $this->marshalKey($alias) : null;

        if ( ! $this->ensure($class) && ! $this->has($alias) && ! $this->nextServant instanceof ServantInterface ) {
            throw new UnresolvedDependencyException('Unresolved dependency: '.$class);
        }

        if ( ! $this->ensure($class) && ! $this->has($alias) ) {
            return $this->nextServant->resolve($class, $alias);
        }

        if ( $this->ensure($class) ) {
            return $this->call($this->interfaces[$className]);
        }

        if ( $this->has($alias) ) {
            return $this->call($this->aliases[$aliasName]);
        }

        throw new UnresolvedDependencyException('Unresolved dependency: '. $class);
    }

    /**
     * Resolves the given reflected parameter.
     *
     * @param ReflectionParameter $parameter
     * @throws UnresolvedDependencyException when the dependency can not be resolved
     * @return mixed
     */
    public function resolveFromReflection(ReflectionParameter $parameter)
    {
        if ( ! $parameter->getClass() ) {
            throw new ServantException('Reflection parameter must hold a class type hint: '.$parameter->getName());
        }

        $className = $this->marshalKey($parameter->getClass()->getName());
        $aliasName = $this->marshalKey($parameter->getName());

        if ( ! $this->ensure($parameter->getClass()->getName()) && ! $this->has($parameter->getName()) && ! $this->nextServant instanceof ServantInterface ) {
            throw new UnresolvedDependencyException('Unresolved dependency: '.$parameter->getClass()->getName());
        }

        if ( ! $this->ensure($parameter->getClass()->getName()) && ! $this->has($parameter->getName()) ) {
            return $this->nextServant->resolveFromReflection($parameter);
        }

        if ( $this->ensure($parameter->getClass()->getName()) ) {
            return $this->call($this->interfaces[$className]);
        }

        if ( $this->has($parameter->getName()) ) {
            return $this->call($this->aliases[$aliasName]);
        }

        throw new UnresolvedDependencyException('Unresolved dependency'.$parameter->getClass()->getName());
    }

    /**
     * marshals a key.
     *
     * @param string $key
     * @return string
     */
    protected function marshalKey(string $key): string
    {
        return strtolower($key);
    }

    /**
     * calls a dependency closure.
     *
     * @param \Closure $closure
     */
    protected function call(\Closure $closure)
    {
        $closureParameters = (new ReflectionFunction($closure))->getParameters();

        call_user_func($closure, $this->fulfill(... $closureParameters));
    }

    /**
     * fulfills the provided parameters.
     *
     * @param ReflectionParameter[] ...$parameters
     * @return \Generator
     */
    protected function fulfill(ReflectionParameter ... $parameters): \Generator
    {
        foreach ( $parameters as $current ) {
            if ( $current->getClass() && $this->ensure($current->getClass()->getName()) ) {
                yield $this->interfaces[
                    $this->marshalKey($current->getClass()->getName())
                ];

                continue;
            }

            if ( $this->has($current->getName()) ) {
                yield $this->aliases[
                    $this->marshalKey($current->getName())
                ];

                continue;
            }

            if ( $current->getClass() && $this->nextServant instanceof ServantInterface ) {
                try {
                    yield $this->nextServant->resolveFromReflection($current);

                    continue;
                }
                catch(ServantException $exception) {
                    // nothing to see... uhm.. do here
                }
            }

            if ( $current->isOptional() ) {
                yield $current->getDeclaringClass()->isInternal()
                    ? null
                    : $current->getDefaultValue()
                ;

                continue;
            }

            throw new UnresolvedDependencyException(
                'Automatic resolving of dependency `'.$current->getClass().'` failed.'
            );
        }
    }
}