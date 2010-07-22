<?php

namespace Bundle\SimpleCASBundle\Adapter;

/**
 * Database adapter.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
interface Adapter
{
    /**
     * Return the user object for the given principal identifier.
     *
     * @param string $principal
     * @return object
     */
    public function getUserByPrincipal($principal);
}
