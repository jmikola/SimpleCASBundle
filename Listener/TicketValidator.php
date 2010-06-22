<?php

namespace Bundle\SimpleCASBundle\Listener;

use Symfony\Components\HttpKernel\HttpKernelInterface;
use Symfony\Components\HttpKernel\LoggerInterface;
use Symfony\Components\EventDispatcher\EventDispatcher;
use Symfony\Components\EventDispatcher\Event;
use Bundle\SimpleCASBundle\SimpleCAS;

/**
 * TicketValidator listens to the core.request event and validates a CAS ticket
 * if one is found in the request's GET parameters.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class TicketValidator
{
    const TICKET = 'ticket';

    protected $simplecas;

    public function __construct(SimpleCAS $simplecas, LoggerInterface $logger)
    {
        $this->simplecas = $simplecas;
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

        $request = $event->getParameter('request');

        if ($ticket = $request->query->get(self::TICKET)) {
            if ($this->simplecas->validateTicket($ticket)) {
                if (null !== $this->logger) {
                    $this->logger->info(sprintf('Validated CAS ticket for principal identifier "%s"', $this->simplecas->getAuthenticatedUid()));
                }
            } else {
                if (null !== $this->logger) {
                    $this->logger->err(sprintf('Invalid CAS ticket "%s" for request: %s', $request->getPathInfo()));
                }
            }
        }
    }
}
