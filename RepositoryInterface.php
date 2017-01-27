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


interface RepositoryInterface
{
    /**
     * sets a concrete for the given alias.
     *
     * @param string $alias
     * @param string|object|\Closure $concrete
     * @return void
     */
    public function set(string $alias, $concrete);

    /**
     * checks whether a given alias is stored or not.
     *
     * @param string[] ...$alias
     * @return bool
     */
    public function has(string ... $alias): bool;

    /**
     * sets a concrete for the given interface.
     *
     * @param string $interface
     * @param $concrete
     * @return void
     */
    public function force(string $interface, $concrete);

    /**
     * checks whether the provided interfaces are known or not.
     *
     * @param \string[] ...$interfaces
     * @return bool
     */
    public function ensure(string ... $interfaces): bool;

    /**
     * sets the given classes as its own interface bindings.
     *
     * @param \string[] ...$classes
     * @return void
     */
    public function acknowledge(string ... $classes);
}