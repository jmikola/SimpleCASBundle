<?php

namespace Bundle\SimpleCASBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;
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

    /**
     * @var Bundle\SimpleCASBundle\SimpleCAS
     */
    protected $simplecas;

    /**
     * @var Symfony\Component\HttpFoundation\Response
     */
    protected $response;

    /**
     * @var Symfony\Component\HttpKernel\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Construct a TicketValidator request listener.
     *
     * @param Bundle\SimpleCASBundle\SimpleCAS                 $simplecas
     * @param Symfony\Component\HttpFoundation\Response        $response
     * @param Symfony\Component\HttpKernel\Log\LoggerInterface $logger
     * @return TicketValidator
     */
    public function __construct(SimpleCAS $simplecas, Response $response, LoggerInterface $logger = null)
    {
        $this->simplecas = $simplecas;
        $this->response = $response;
        $this->logger = $logger;
    }

    /**
     * Registers a core.request listener.
     *
     * @param Symfony\Component\EventDispatcher\EventDispatcher $dispatcher
     */
    public function register(EventDispatcher $dispatcher)
    {
        $dispatcher->connect('core.request', array($this, 'handle'));
    }

    /**
     * Validates the CAS ticket if one is found in the request's GET parameters.
     *
     * @param Symfony\Component\EventDispatcher\Event $event
     */
    public function handle(Event $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->get('request_type')) {
            return;
        }

        if ($this->simplecas) {
            $request = $event->get('request');

            if ($ticket = $request->query->get(static::TICKET)) {
                if ($this->simplecas->validateTicket($ticket)) {
                    $this->simplecas->removeLoginRedirectUrl();
                    if (null !== $this->logger) {
                        $this->logger->info(sprintf('Validated CAS ticket "%s" for principal identifier "%s"', $ticket, $this->simplecas->getUid()));
                    }
                } else {
                    if (null !== $this->logger) {
                        $this->logger->err(sprintf('Invalid CAS ticket "%s" for request: %s', $ticket, $request->getPathInfo()));
                    }
                }

                $this->response->setStatusCode(302);
                $this->response->headers->set('Location', $this->simplecas->getCurrentUrl());
                $event->setReturnValue($this->response);
                return true;
            }
        }
    }
}
