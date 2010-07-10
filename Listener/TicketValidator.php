<?php

namespace Bundle\SimpleCASBundle\Listener;

use Symfony\Components\DependencyInjection\ContainerInterface;
use Symfony\Components\HttpKernel\HttpKernelInterface;
use Symfony\Components\HttpKernel\LoggerInterface;
use Symfony\Components\EventDispatcher\EventDispatcher;
use Symfony\Components\EventDispatcher\Event;

/**
 * TicketValidator listens to the core.request event and validates a CAS ticket
 * if one is found in the request's GET parameters.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class TicketValidator
{
    const TICKET = 'ticket';

    protected $container;
    protected $logger;

    public function __construct(ContainerInterface $container, LoggerInterface $logger = null)
    {
        $this->container = $container;
        $this->logger = $logger;
    }

    /**
     * Registers a core.request listener.
     *
     * @param Symfony\Components\EventDispatcher\EventDispatcher $dispatcher
     */
    public function register(EventDispatcher $dispatcher)
    {
        $dispatcher->connect('core.request', array($this, 'handle'));
    }

    /**
     * Validates the CAS ticket if one is found in the request's GET parameters.
     *
     * @param Symfony\Components\EventDispatcher\Event $event
     */
    public function handle(Event $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getParameter('request_type')) {
            return;
        }

        if ($this->container->hasService('simplecas')) {
            $simplecas = $this->container->getService('simplecas');
            $request = $event->getParameter('request');

            if ($ticket = $request->query->get(self::TICKET)) {
                if ($simplecas->validateTicket($ticket)) {
                    if (null !== $this->logger) {
                        $this->logger->info(sprintf('Validated CAS ticket "%s" for principal identifier "%s"', $ticket, $simplecas->getUid()));
                    }
                } else {
                    if (null !== $this->logger) {
                        $this->logger->err(sprintf('Invalid CAS ticket "%s" for request: %s', $ticket, $request->getPathInfo()));
                    }
                }

                $response = $this->container->getService('response');
                $response->setStatusCode(302);
                $response->headers->set('Location', $simplecas->getCurrentUrl());
                $event->setReturnValue($response);
                return true;
            }
        }
    }
}
