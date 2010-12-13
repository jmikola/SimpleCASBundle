<?php

namespace Bundle\SimpleCASBundle;

use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\HttpKernel\HttpKernel;
use Bundle\SimpleCASBundle\Adapter\Adapter;
use Bundle\SimpleCASBundle\Exception\NoUserForPrincipalException;

/**
 * Client class for authenticating users against a CAS server using SimpleCAS.
 *
 * This is a replacement for the \SimpleCAS class that ships with the SimpleCAS
 * library.  It implements a friendly interface for controller actions and
 * integrates with Symfony's dependency injection container.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class SimpleCAS
{
    /**
     * Session attribute for the CAS principal identifier.
     */
    const UID = '__SIMPLECAS_UID';

    /**
     * Session attribute for login action redirection.
     */
    const LOGIN_REDIRECT_URL = '__SIMPLECAS_LOGIN_REDIRECT_URL';

    /**
     * CAS service protocol.
     *
     * @var \SimpleCAS_Protocol
     */
    protected $protocol;

    /**
     * HTTP kernel object.
     *
     * @var Symfony\Component\HttpKernel\HttpKernel
     */
    protected $kernel;

    /**
     * User session service.
     *
     * @var Symfony\Bundle\FrameworkBundle\User
     */
    protected $session;

    /**
     * Database adapter.
     *
     * @var Bundle\SimpleCASBundle\Adapter\Adapter
     */
    protected $adapter;

    /**
     * Is user authenticated?
     *
     * @var boolean
     */
    protected $authenticated = false;

    /**
     * Construct a CAS client object.
     *
     * If the session contains a CAS principal identifier, the current session
     * will be considered authenticated.
     *
     * @param \SimpleCAS_Protocol                      $protocol
     * @param Symfony\Component\HttpKernel\HttpKernel  $kernel
     * @param Symfony\Component\HttpFoundation\Session $session
     * @param Bundle\SimpleCASBundle\Adapter\Adapter   $adapter
     * @return SimpleCAS
     */
    public function __construct(\SimpleCAS_Protocol $protocol, HttpKernel $kernel, Session $session, Adapter $adapter = null)
    {
        $this->protocol = $protocol;
        $this->kernel = $kernel;
        $this->session = $session;
        $this->adapter = $adapter;

        if ($this->session->get(static::UID)) {
            $this->authenticated = true;
        }
    }

    /**
     * Validate a CAS sign-on ticket.
     *
     * Attempt to authenticate the current session by verifying the ticket
     * against the CAS server and return whether the user is now authenticated.
     *
     * @param string $ticket
     * @return boolean
     */
    public function validateTicket($ticket)
    {
        if ($uid = $this->protocol->validateTicket($ticket, $this->getCurrentUrl())) {
            $this->authenticate($uid);
            return true;
        } else {
            $this->unauthenticate();
            return false;
        }
    }

    /**
     * Check if the current session is authenticated.
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        return $this->authenticated;
    }

    /**
     * Return the authenticated user's principal identifier.
     *
     * @return string
     */
    public function getUid()
    {
        return $this->session->get(static::UID);
    }

    /**
     * Marks the current session as authenticated for a principal identifier.
     *
     * This method may be used to force the authentication state of the user
     * without requiring validation against the CAS server.
     *
     * @param string $uid Principal identifier for the authenticated user
     * @return SimpleCAS
     */
    public function authenticate($uid)
    {
        $this->session->set(static::UID, $uid);
        $this->authenticated = true;
        return $this;
    }

    /**
     * Marks the current session as unauthenticated.
     *
     * @return SimpleCAS
     */
    public function unauthenticate()
    {
        $this->session->remove(static::UID);
        $this->authenticated = false;
        return $this;
    }


    /**
     * Return the database object for the authenticated user or null if the
     * current user is not authenticated.
     *
     * This method will throw a BadMethodCallException if no database adapter is
     * available.  A NoUserForPrincipalException will be thrown if no user
     * object can be found for the principal.
     *
     * @return object
     * @throws \BadMethodCallException
     * @throws Bundle\SimpleCASBundle\Exception\NoUserForPrincipalException
     */
    public function getUser()
    {
        if (!$this->adapter) {
            throw new \BadMethodCallException('SimpleCAS database adapter is not configured');
        }

        if ($this->authenticated) {
            if ($user = $this->adapter->getUserByPrincipal($this->getUid())) {
                return $user;
            } else {
                throw new NoUserForPrincipalException(sprintf('No user object found for principal identifier "%s"', $this->getUid()));
            }
        } else {
            return null;
        }
    }

    /**
     * Return the database object for the authenticated user or redirect to the
     * CAS server's login URL if the current user is not authenticated.
     *
     * A convenience method that wraps successive calls to requireLogin() and
     * getUser().
     *
     * @return object
     */
    public function requireUser()
    {
        return $this->requireLogin()->getUser();
    }

    /**
     * Return the authenticated user's principal identifier or redirect to the
     * CAS server's login URL if the current user is not authenticated.
     *
     * A convenience method that wraps successive calls to requireLogin() and
     * getUid().
     *
     * @return string
     */
    public function requireUid()
    {
        return $this->requireLogin()->getUid();
    }

    /**
     * Require authentication for the current user.
     *
     * Redirect to the CAS server's login URL if the current user is not
     * authenticated.  Otherwise, return this CAS client object.
     *
     * @return SimpleCAS
     */
    public function requireLogin()
    {
        if (!$this->authenticated) {
            $this->redirect($this->getLoginUrl());
        }
        return $this;
    }

    /**
     * Force the current user to logout if currently authenticated.
     *
     * Redirect to the CAS server's logout URL if the current user is
     * authenticated.  Otherwise, return this CAS client object.
     *
     * @return SimpleCAS
     */
    public function requireLogout()
    {
        if ($this->authenticated) {
            $this->unauthenticate()->redirect($this->getLogoutUrl());
        }
        return $this;
    }

    /**
     * Return the CAS server's login URL.
     *
     * The service URL is optional and will default to the current URL.
     *
     * @param string $url
     * @return string
     */
    public function getLoginUrl($url = null)
    {
        return $this->protocol->getLoginURL($url ?: $this->getCurrentUrl());
    }

    /**
     * Return the CAS server's logout URL.
     *
     * The service URL is optional and will default to the current URL.
     *
     * @param string $url
     * @return string
     */
    public function getLogoutUrl($url = null)
    {
        return $this->protocol->getLogoutURL($url ?: $this->getCurrentUrl());
    }

    /**
     * Returns the current URL without CAS-affecting parameters.
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        $replacements = array(
            '/\?logout/'        => '',
            '/&ticket=[^&]*/'   => '',
            '/\?ticket=[^&;]*/' => '?',
            '/\?%26/'           => '?',
            '/\?&/'             => '?',
            '/\?$/'             => '',
        );
        $request = $this->kernel->getRequest();
        $uri = $this->request->getScheme() . '://' . $this->request->getHost() . $this->request->getRequestUri();

        return preg_replace(array_keys($replacements), array_values($replacements), $uri);
    }

    /**
     * Gets the login redirect URL from the session.
     *
     * @param mixed $default
     * @return string
     */
    public function getLoginRedirectUrl($default = null)
    {
        return $this->session->get(static::LOGIN_REDIRECT_URL, $default);
    }

    /**
     * Sets the login redirect URL in the session.
     *
     * @param string $loginRedirectUrl
     * @return SimpleCAS
     */
    public function setLoginRedirectUrl($loginRedirectUrl)
    {
        $this->session->set(static::LOGIN_REDIRECT_URL, $loginRedirectUrl);
        return $this;
    }

    /**
     * Remove the login redirect URL from the session.
     *
     * @return SimpleCAS
     */
    public function removeLoginRedirectUrl()
    {
        $this->session->remove(static::LOGIN_REDIRECT_URL);
        return $this;
    }

    /**
     * Redirect the client to another URL.
     *
     * @param string $url
     */
    protected function redirect($url)
    {
        // TODO: Refactor once Symfony supports redirect exceptions
        header('Location: ' . $url);
        exit();
    }
}
