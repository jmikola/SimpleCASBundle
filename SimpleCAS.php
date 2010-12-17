<?php

namespace Bundle\SimpleCASBundle;

/**
 * Client class for authenticating users against a CAS server using SimpleCAS.
 *
 * This is a replacement for the \SimpleCAS class that ships with the SimpleCAS
 * library.  It wraps essential protocol functionality and integrates with
 * Symfony's dependency injection container.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class SimpleCAS
{
    /**
     * CAS service protocol.
     *
     * @var \SimpleCAS_Protocol
     */
    protected $protocol;

    /**
     * Construct a CAS client object.
     *
     * @param \SimpleCAS_Protocol $protocol
     */
    public function __construct(\SimpleCAS_Protocol $protocol)
    {
        $this->protocol = $protocol;
    }

    /**
     * Validate a CAS sign-on ticket against the CAS server.
     *
     * @param string $ticket
     * @param string $service
     * @return string|boolean Principal identifier if validation succeeds, false otherwise
     */
    public function validateTicket($ticket, $service)
    {
        return $this->protocol->validateTicket($ticket, $service);
    }

    /**
     * Return a CAS login URL.
     *
     * @param string $service Service URL
     * @return string
     */
    public function getLoginUrl($service)
    {
        return $this->protocol->getLoginURL($service);
    }

    /**
     * Return a CAS logout URL.
     *
     * The service URL is optional and will only be used if logout service
     * redirection is enabled for the protocol.
     *
     * @param string $service Service URL
     * @return string
     */
    public function getLogoutUrl($service = null)
    {
        return $this->protocol->getLogoutURL($service);
    }

    /**
     * Strips CAS parameters from a URL's query string.
     *
     * @param string $url
     * @return string
     */
    public function cleanUrl($url)
    {
        $replacements = array(
            '/\?logout/'        => '',
            '/&ticket=[^&]*/'   => '',
            '/\?ticket=[^&;]*/' => '?',
            '/\?%26/'           => '?',
            '/\?&/'             => '?',
            '/\?$/'             => '',
        );

        return preg_replace(array_keys($replacements), array_values($replacements), $url);
    }
}
