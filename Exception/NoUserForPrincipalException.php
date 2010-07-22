<?php

namespace Bundle\SimpleCASBundle\Exception;

/**
 * NoUserForPrincipalException.
 *
 * This exception is thrown when an adapter class cannot find a user object for
 * an authenticated principal identifier.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class NoUserForPrincipalException extends \Exception
{
}
