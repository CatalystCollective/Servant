<?php
/**
 * This file is part of the Catalyst Swoole Foundation.
 *
 * (c)2017 Matthias Kaschubowski
 *
 * This code is licensed under the MIT license,
 * a copy of the license is stored at the project root.
 */

namespace Catalyst\Servant;


use Catalyst\Servant\Exceptions\ServantException;

interface ServantAwareInterface
{
    /**
     * sets or pulls the current servant interface.
     *
     * @param ServantInterface|null $servant
     * @throws ServantException when not servant instance is given and the command must pull one.
     * @return ServantInterface
     */
    public function servant(ServantInterface $servant = null): ServantInterface;
}