<?php

namespace Bundle\SimpleCASBundle\Security\Token;

use Symfony\Component\Security\Authentication\Token\Token;

/**
 * SimpleCASToken implements a CAS ticket token.
 *
 * Before CAS ticket validation has occurred, this token will only contain the
 * ticket as its credentials.  After validation, a user and roles will be set.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class SimpleCASToken extends Token
{
    /**
     * Constructor.
     *
     * @param object $user        User object
     * @param string $credentials CAS ticket
     * @param array  $roles       An array of roles
     */
    public function __construct($user, $credentials, array $roles = array())
    {
        parent::__construct($roles);

        $this->user = $user;
        $this->credentials = $credentials;

        parent::setAuthenticated((boolean) count($roles));
    }

    /**
     * Sets the authenticated flag.
     *
     * @param boolean $isAuthenticated The authenticated flag
     * @throws \LogicException if $isAuthenticated is true
     */
    public function setAuthenticated($isAuthenticated)
    {
        if ($isAuthenticated) {
            throw new \LogicException('Cannot set this token to trusted after instantiation.');
        }

        parent::setAuthenticated(false);
    }

    /**
     * Removes sensitive information from the token.
     */
    public function eraseCredentials()
    {
        parent::eraseCredentials();

        $this->credentials = null;
    }
}
