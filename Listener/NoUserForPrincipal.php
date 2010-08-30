<?php

namespace Bundle\SimpleCASBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Bundle\SimpleCASBundle\Exception\NoUserForPrincipalException;

/**
 * NoUserForPrincipal listens to the core.exception event and unauthenticates
 * a user both locally and remotely (against the CAS server) if SimpleCAS is
 * unable to find a user object for a principal identifier.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class NoUserForPrincipal
{
    protected $container;
    protected $logger;

    public function __construct(ContainerInterface $container, LoggerInterface $logger = null)
    {
        $this->container = $container;
        $this->logger = $logger;
    }

    /**
     * Registers a core.exception listener.
     *
     * @param Symfony\Component\EventDispatcher\EventDispatcher $dispatcher
     */
    public function register(EventDispatcher $dispatcher)
    {
        $listeners = $dispatcher->getListeners('core.exception');
        $dispatcher->connect('core.exception', array($this, 'handle'));

        // Reconnect all other core.exception listeners to ensure we're first
        foreach ($listeners as $listener) {
            $dispatcher->disconnect('core.exception', $listener);
            $dispatcher->connect('core.exception', $listener);
        }
    }

    /**
     * Checks for a NoUserForPrincipalException and unauthenticates the user
     * locally and then remotely by redirecting to the CAS logout URL.
     *
     * @param Symfony\Component\EventDispatcher\Event $event
     */
    public function handle(Event $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getParameter('request_type')) {
            return;
        }

        if ($this->container->has('simplecas')) {
            $exception = $event->getParameter('exception');

            if ($exception instanceof NoUserForPrincipalException) {
                if (null !== $this->logger) {
                    $this->logger->err(sprintf('Redirecting to CAS logout page (%s)', $exception->getMessage()));
                }

                $simplecas = $this->container->get('simplecas');
                $simplecas->unauthenticate();

                $response = $this->container->get('response');
                $response->setStatusCode(302);
                $response->headers->set('Location', $simplecas->getLogoutUrl());
                $event->setReturnValue($response);
                return true;
            }
        }
    }
}
