<?php

namespace Bundle\SimpleCASBundle\Security\Firewall;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Security\Firewall\ListenerInterface;
use Symfony\Component\Security\SecurityContext;
use Symfony\Component\Security\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Exception\AuthenticationException;
use Bundle\SimpleCASBundle\SimpleCAS;
use Bundle\SimpleCASBundle\Security\Token\SimpleCASToken;

/**
 * CAS authentication listener.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class SimpleCASAuthenticationListener implements ListenerInterface
{
    protected $securityContext;
    protected $authenticationManager;
    protected $simplecas;
    protected $logger;

    /**
     * Constructor.
     *
     * @param SecurityContext                $securityContext       A SecurityContext instance
     * @param AuthenticationManagerInterface $authenticationManager An AuthenticationManagerInterface instance
     * @param SimpleCAS                      $simplecas             A CAS client instance
     * @param LoggerInterface                $logger                A LoggerInterface instance
     */
    public function __construct(SecurityContext $securityContext, AuthenticationManagerInterface $authenticationManager, SimpleCAS $simplecas, LoggerInterface $logger = null)
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->simplecas = $simplecas;
        $this->logger = $logger;
    }

    /**
     * Connects this listener to the "core.security" event.
     *
     * @param EventDispatcher $dispatcher An EventDispatcher instance
     * @param integer         $priority   The priority
     */
    public function register(EventDispatcher $dispatcher, $priority = 0)
    {
        $dispatcher->connect('core.security', array($this, 'handle'), $priority);
    }

    /**
     * Disconnects this listener from the "core.security" event.
     *
     * @param EventDispatcher $dispatcher An EventDispatcher instance
     */
    public function unregister(EventDispatcher $dispatcher)
    {
        $dispatcher->disconnect('core.security', array($this, 'handle'));
    }

    /**
     * Handles CAS based authentication.
     *
     * @param Event $event An Event instance
     */
    public function handle(Event $event)
    {
        $request = $event->get('request');

        if (!$ticket = $request->query->get('ticket')) {
            return;
        }

        try {
            $token = $this->authenticationManager->authenticate(new SimpleCASToken(null, $ticket));

            if (null !== $this->logger) {
                $this->logger->debug(sprintf('Authentication success: %s', $token));
            }

            $this->securityContext->setToken($token);
        } catch (AuthenticationException $failed) {
            $this->securityContext->setToken(null);

            if (null !== $this->logger) {
                $this->logger->debug(sprintf("Cleared security context due to exception: %s", $failed->getMessage()));
            }
        }

        $response = new Response();
        $response->setRedirect($this->simplecas->cleanUrl($request->getUri()));
        $event->setReturnValue($response);

        return true;
    }
}
