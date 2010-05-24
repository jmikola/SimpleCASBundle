<?php

namespace Bundle\SimpleCASBundle;

use Symfony\Framework\WebBundle\User;

/**
 * This is a CAS client authentication library for PHP 5.
 *
 * This is a drop-in replacement for the package \SimpleCAS class, which
 * provides support for Symfony 2 sessions.
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
     * Singleton CAS object
     *
     * @var SimpleCAS
     */
    static private $_instance;

    /**
     * User session service.
     *
     * @var Symfony\Framework\WebBundle\User
     */
    private $_user;

    /**
     * Is user authenticated?
     *
     * @var bool
     */
    private $_authenticated = false;

    /**
     * Protocol for the server running the CAS service.
     *
     * @var \SimpleCAS_Protocol
     */
    protected $protocol;

    /**
     * User's login name if authenticated.
     *
     * @var string
     */
    protected $username;

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
     */
    private function __construct(\SimpleCAS_Protocol $protocol, User $user)
    {
        $this->protocol = $protocol;

        if ($this->protocol instanceof \SimpleCAS_SingleSignOut && isset($_POST)) {
            if ($ticket = $this->protocol->validateLogoutRequest($_POST)) {
                $this->logout($ticket);
            }
        }

        $this->_user = $user;

        if ($this->_user->getAttribute(self::TICKET)) {
            $this->_authenticated = true;
        }

        if ($this->_authenticated == false && isset($_GET['ticket'])) {
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
     * @return bool
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
     * @param string $uid User name returned by the CAS server.
     */
    protected function setAuthenticated($uid)
    {
        $this->_user->setAttribute(self::TICKET, true);
        $this->_user->setAttribute(self::UID, $uid);
        $this->_authenticated = true;
    }

    /**
     * Return the authenticated user's login name.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->_user->getAttribute(static::UID);
    }

    /**
     * Singleton interface, returns CAS object.
     *
     * @param \SimpleCAS_Protocol              $server CAS Server object
     * @param Symfony\Framework\WebBundle\User $user   User session service
     * @return SimpleCAS
     */
    static public function client(\SimpleCAS_Protocol $protocol, User $user)
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self($protocol, $user);
        }

        return self::$_instance;
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
     * @return bool
     */
    public function isAuthenticated()
    {
        return $this->_authenticated;
    }

    /**
     * Destroys session data for this client, redirects to the server logout
     * url.
     *
     * @param string $url URL to provide the client on logout.
     *
     * @return void
     */
    public function logout($url = '')
    {
        $this->_user->removeAttribute(self::TICKET);
        $this->_user->removeAttribute(self::UID);

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
        if (isset($_SERVER['HTTPS'])
        && !empty($_SERVER['HTTPS'])
        && $_SERVER['HTTPS'] == 'on') {
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

        $url = preg_replace(array_keys($replacements),
        array_values($replacements), $url);

        return $url;
    }

    /**
     * Set an alternative return URL
     *
     * @param string $url alternative return URL
     *
     * @return void
     */
    static public function setURL($url)
    {
        self::$url = $url;
    }

    /**
     * Send a header to redirect the client to another URL.
     *
     * @param string $url URL to redirect the client to.
     *
     * @return void
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