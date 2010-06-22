<?php

namespace Bundle\SimpleCASBundle;

use Symfony\Components\HttpKernel\Request;
use Symfony\Framework\WebBundle\User;

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
     * CAS service protocol.
     *
     * @var \SimpleCAS_Protocol
     */
    protected $protocol;

    /**
     * HTTP request object.
     *
     * @var Symfony\Components\HttpKernel\Request
     */
    protected $request;

    /**
     * User session service.
     *
     * @var Symfony\Framework\WebBundle\User
     */
    protected $user;

    /**
     * Is user authenticated?
     *
     * @var boolean
     */
    protected $authenticated = false;

    /**
     * Default
     */

    /**
     * Construct a CAS client object.
     *
     * If the session contains a CAS principal identifier, the current session
     * will be considered authenticated.
     *
     * @param \SimpleCAS_Protocol                   $protocol
     * @param Symfony\Components\HttpKernel\Request $request
     * @param Symfony\Framework\WebBundle\User      $user
     * @return SimpleCAS
     */
    public function __construct(\SimpleCAS_Protocol $protocol, Request $request, User $user)
    {
        $this->protocol = $protocol;
        $this->request = $request;
        $this->user = $user;

        if ($this->user->getAttribute(self::UID)) {
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
        if ($uid = $this->protocol->validateTicket($ticket, self::getURL())) {
            $this->setAuthenticated($uid);
            return true;
        } else {
            $this->removeAuthentication();
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
    public function getAuthenticatedUid()
    {
        return $this->user->getAttribute(static::UID);
    }

    /**
     * Marks the current session as authenticated.
     *
     * This method may be used to force the authentication state of the user
     * without requiring validation against the CAS server.
     *
     * @param string $uid Principal identifier for the authenticated user
     * @return SimpleCAS
     */
    public function setAuthenticatedUid($uid)
    {
        $this->user->setAttribute(self::UID, $uid);
        $this->authenticated = true;
        return $this;
    }

    /**
     * Marks the current session as unauthenticated.
     *
     * @return SimpleCAS
     */
    public function removeAuthentication()
    {
        $this->user->removeAttribute(self::UID);
        $this->authenticated = false;
        return $this;
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
            $this->removeAuthentication()->redirect($this->getLogoutUrl());
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
     * @return string url
     */
    public function getCurrentURL()
    {
        $replacements = array(
            '/\?logout/'        => '',
            '/&ticket=[^&]*/'   => '',
            '/\?ticket=[^&;]*/' => '?',
            '/\?%26/'           => '?',
            '/\?&/'             => '?',
            '/\?$/'             => '',
        );
        return preg_replace(array_keys($replacements), array_values($replacements), $this->request->getUri());
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
