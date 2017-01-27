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

interface ServantInterface
{
    /**
     * Adds the next servant interface to call when the class dependency can not be resolved by the actual
     * Servant instance.
     *
     * @param ServantInterface $next
     * @return ServantInterface
     */
    public function chain(ServantInterface $next): ServantInterface;

    /**
     * returns the next servant in chain.
     *
     * @throws ServantException if no servant is present.
     * @return ServantInterface
     */
    public function next(): ServantInterface;

    /**
     * Resolves the given class and optional alias.
     *
     * @param string $class
     * @param string|null $alias
     * @throws UnresolvedDependencyException when the dependency can not be resolved
     * @return object
     */
    public function resolve(string $class, string $alias = null);

    /**
     * Resolves the given reflected parameter.
     *
     * @param ReflectionParameter $parameter
     * @throws UnresolvedDependencyException when the dependency can not be resolved
     * @return mixed
     */
    public function resolveFromReflection(ReflectionParameter $parameter);
}