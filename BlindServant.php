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


use Catalyst\Servant\Exceptions\UnresolvedDependencyException;
use ReflectionParameter;

class BlindServant extends ServantAbstract implements ServantInterface
{
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
        if ( class_exists($class, true) ) {
            return new $class;
        }

        if ( $this->nextServant instanceof ServantInterface ) {
            return $this->nextServant->resolve($class, $alias);
        }

        throw new UnresolvedDependencyException('Unresolvable dependency: '.$class);
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
        $class = $parameter->getClass()->getName();

        if ( class_exists($class, true) ) {
            return new $class;
        }

        if ( $this->nextServant instanceof ServantInterface ) {
            return $this->nextServant->resolveFromReflection($parameter);
        }

        throw new UnresolvedDependencyException('Unresolvable dependency: '.$class);
    }

}