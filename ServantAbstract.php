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

abstract class ServantAbstract implements ServantInterface
{
    /**
     * @var null|ServantInterface
     */
    protected $nextServant = null;

    /**
     * Adds the next servant interface to call when the class dependency can not be resolved by the actual
     * Servant instance.
     *
     * @param ServantInterface $next
     * @return ServantInterface
     */
    public function chain(ServantInterface $next): ServantInterface
    {
        return $this->nextServant = $next;
    }

    /**
     * returns the next servant in chain.
     *
     * @throws ServantException if no servant is present.
     * @return ServantInterface
     */
    public function next(): ServantInterface
    {
        if ( $this->nextServant instanceof ServantInterface ) {
            return $this->nextServant;
        }

        throw new ServantException('No more servants');
    }
}