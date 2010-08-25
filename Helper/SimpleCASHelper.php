<?php

namespace Bundle\SimpleCASBundle\Helper;

use Symfony\Component\Templating\Helper\Helper;
use Bundle\SimpleCASBundle\SimpleCAS;
use Bundle\SimpleCASBundle\Exception\NoUserForPrincipalException;

/**
 * SimpleCASHelper acts as a proxy for getter methods on the SimpleCAS client
 * object, which allows for convenient access from within a template.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class SimpleCASHelper extends Helper
{
    /**
     * SimpleCAS client instance.
     *
     * @var SimpleCAS
     */
    protected $simplecas;

    /**
     * Constructor.
     *
     * @param SimpleCAS $simplecas
     * @return SimpleCASHelper
     */
    public function __construct(SimpleCAS $simplecas)
    {
        $this->simplecas = $simplecas;
    }

    /**
     * Check if the current session is authenticated.
     *
     * @see SimpleCAS::isAuthenticated()
     * @return boolean
     */
    public function isAuthenticated()
    {
        return $this->simplecas->isAuthenticated();
    }

    /**
     * Return the authenticated user's principal identifier.
     *
     * @see SimpleCAS::getUid()
     * @return string
     */
    public function getUid()
    {
        return $this->simplecas->getUid();
    }

    /**
     * Return the database object for the authenticated user or null if the
     * current user is not authenticated.
     *
     * This method will throw a BadMethodCallException if no database adapter is
     * available.  If no user object can be found for an authenticated user's
     * principal, this method will catch the NoUserForPrincipalException from
     * the client class and return null.
     *
     * @return object
     * @throws \BadMethodCallException
     */
    public function getUser()
    {
        try {
            return $this->simplecas->getUser();
        } catch (NoUserForPrincipalException $e) {
            return null;
        }
    }

    /**
     * Return the CAS server's login URL.
     *
     * The service URL is optional and will default to the current URL.
     *
     * @see SimpleCAS::getLoginUrl()
     * @param string $url
     * @return string
     */
    public function getLoginUrl($url = null)
    {
        return $this->simplecas->getLoginUrl($url);
    }

    /**
     * Return the CAS server's logout URL.
     *
     * The service URL is optional and will default to the current URL.
     *
     * @see SimpleCAS::getLogoutUrl()
     * @param string $url
     * @return string
     */
    public function getLogoutUrl($url = null)
    {
        return $this->simplecas->getLogoutUrl($url);
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string
     */
    public function getName()
    {
        return 'simplecas';
    }
}
