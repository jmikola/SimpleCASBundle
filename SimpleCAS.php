<?php

namespace Bundle\SimpleCASBundle;

use Symfony\Framework\WebBundle\User;

/**
 * This is a CAS client authentication library for PHP 5.
 *
 * This is a replacement for the \SimpleCAS class, which adds support for
 * Symfony's User session service.  The singleton interface has also been
 * removed in favor of shared service management by Symfony's dependency
 * injection container.
 *
 * @category  Authentication
 * @package   SimpleCAS
 * @author    Brett Bieber <brett.bieber@gmail.com>
 * @copyright 2008 Regents of the University of Nebraska
 * @license   http://www1.unl.edu/wdn/wiki/Software_License BSD License
 * @link      http://code.google.com/p/simplecas/
 *
 * @author    Jeremy Mikola <jmikola@gmail.com>
 */
class SimpleCAS
{
    /**
     * Version of the CAS library.
     */
    const VERSION = '0.0.1';

    /**
     * Session attribute for the CAS ticket.
     */
    const TICKET = '__SIMPLECAS_TICKET';

    /**
     * Session attribute for the CAS principal identifier.
     */
    const UID = '__SIMPLECAS_UID';

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
     * Protocol for the server running the CAS service.
     *
     * @var \SimpleCAS_Protocol
     */
    protected $protocol;

    /**
     * (Optional) alternative service URL to return to after CAS authentication.
     *
     * @var string
     */
    static protected $url;

    /**
     * Construct a CAS client object.
     *
     * @param \SimpleCAS_Protocol              $protocol Protocol to use for authentication.
     * @param Symfony\Framework\WebBundle\User $user     User session service
     * @return SimpleCAS
     */
    public function __construct(\SimpleCAS_Protocol $protocol, User $user)
    {
        $this->protocol = $protocol;

        if ($this->protocol instanceof \SimpleCAS_SingleSignOut && isset($_POST)) {
            if ($ticket = $this->protocol->validateLogoutRequest($_POST)) {
                $this->logout($ticket);
            }
        }

        $this->user = $user;

        if ($this->user->getAttribute(self::TICKET)) {
            $this->authenticated = true;
        }

        if ($this->authenticated == false && isset($_GET['ticket'])) {
            $this->validateTicket($_GET['ticket']);
        }
    }

    /**
     * Checks a ticket to see if it is valid.
     *
     * If the CAS server verifies the ticket, a session is created and the user
     * is marked as authenticated.
     *
     * @param string $ticket Ticket from the CAS Server
     * @return boolean
     */
    protected function validateTicket($ticket)
    {
        if ($uid = $this->protocol->validateTicket($ticket, self::getURL())) {
            $this->setAuthenticated($uid);
            $this->redirect(self::getURL());
            return true;
        } else {
            return false;
        }
    }

    /**
     * Marks the current session as authenticated.
     *
     * @param string $uid User identifier returned by the CAS server.
     */
    protected function setAuthenticated($uid)
    {
        $this->user->setAttribute(self::TICKET, true);
        $this->user->setAttribute(self::UID, $uid);
        $this->authenticated = true;
    }

    /**
     * Return the authenticated user's identifier.
     *
     * @return string
     */
    public function getAuthenticatedUid()
    {
        return $this->user->getAttribute(static::UID);
    }

    /**
     * If client is not authenticated, this will redirect to login and exit.
     *
     * Otherwise, return the CAS object.
     *
     * @return SimpleCAS
     */
    public function forceAuthentication()
    {
        if (!$this->isAuthenticated()) {
            self::redirect($this->protocol->getLoginURL(self::getURL()));
        }

        return $this;
    }

    /**
     * Check if this user has been authenticated or not.
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        return $this->authenticated;
    }

    /**
     * Destroys session data for this client, redirects to the server logout
     * url.
     *
     * @param string $url URL to provide the client on logout.
     */
    public function logout($url = '')
    {
        $this->user->removeAttribute(self::TICKET);
        $this->user->removeAttribute(self::UID);

        if (empty($url)) {
            $url = self::getURL();
        }

        $this->redirect($this->protocol->getLogoutURL($url));
    }

    /**
     * Returns the current URL without CAS affecting parameters.
     *
     * @return string url
     */
    static public function getURL()
    {
        if (!empty(self::$url)) {
            return self::$url;
        }

        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $protocol = 'https';
        } else {
            $protocol = 'http';
        }

        $url = $protocol.'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];

        $replacements = array('/\?logout/'        => '',
                              '/&ticket=[^&]*/'   => '',
                              '/\?ticket=[^&;]*/' => '?',
                              '/\?%26/'           => '?',
                              '/\?&/'             => '?',
                              '/\?$/'             => '');

        $url = preg_replace(array_keys($replacements), array_values($replacements), $url);

        return $url;
    }

    /**
     * Set an alternative return URL
     *
     * @param string $url alternative return URL
     */
    static public function setURL($url)
    {
        self::$url = $url;
    }

    /**
     * Send a header to redirect the client to another URL.
     *
     * @param string $url URL to redirect the client to.
     */
    static public function redirect($url)
    {
        header("Location: $url");
        exit();
    }

    /**
     * Get the version of the CAS library
     *
     * @return string
     */
    static public function getVersion()
    {
        return self::VERSION;
    }
}