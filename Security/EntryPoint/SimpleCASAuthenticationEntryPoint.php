<?php

namespace Bundle\SimpleCASBundle\Security\EntryPoint;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Authentication\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Exception\AuthenticationException;
use Bundle\SimpleCASBundle\SimpleCAS;

/**
 * Entry point for authentication via a CAS server.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class SimpleCASAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    protected $simplecas;

    /**
     * Constructor.
     *
     * @param SimpleCAS $simplecas
     */
    public function __construct(SimpleCAS $simplecas)
    {
        $this->simpleCAS = $simplecas;
    }

    /**
     * Starts the authentication scheme.
     *
     * @param Request                 $request       The request that resulted in an AuthenticationException
     * @param AuthenticationException $authException The exception that started the authentication process
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $response = new Response();
        $response->setRedirect($this->simplecas->getLoginUrl($this->simplecas->cleanUrl($request->getUri())));

        return $response;
    }
}
