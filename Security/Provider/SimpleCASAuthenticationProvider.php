<?php

namespace Bundle\SimpleCASBundle\Security\Provider;

use Symfony\Component\Security\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Exception\BadCredentialsException;
use Symfony\Component\Security\User\UserProviderInterface;
use Symfony\Component\Security\User\AccountCheckerInterface;
use Bundle\SimpleCASBundle\SimpleCAS;
use Bundle\SimpleCASBundle\Security\Token\SimpleCASToken;

/**
 * Processes CAS ticket validation.
 *
 * This authentication provider assumes that the user has already authenticated
 * on the CAS server and has just been redirected back with a ticket parameter.
 * Authentication within this class may still fail if the ticket cannot be
 * successfully validated against the CAS server.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class SimpleCASAuthenticationProvider implements AuthenticationProviderInterface
{
    protected $userProvider;
    protected $accountChecker;
    protected $simplecas;

    /**
     * Constructor.
     *
     * @param UserProviderInterface   $userProvider   A UserProviderInterface instance
     * @param AccountCheckerInterface $accountChecker An AccountCheckerInterface instance
     * @param SimpleCAS               $simplecas      A SimpleCAS instance
     */
    public function __construct(UserProviderInterface $userProvider, AccountCheckerInterface $accountChecker, SimpleCAS $simplecas)
    {
        $this->userProvider = $userProvider;
        $this->accountChecker = $accountChecker;
        $this->simplecas = $simplecas;
    }

    /**
     * Attempts to authenticates a TokenInterface object.
     *
     * @param TokenInterface $token The TokenInterface instance to authenticate
     * @return TokenInterface An authenticated TokenInterface instance
     * @throws BadCredentialsException if the CAS ticket cannot be validated
     */
    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return null;
        }

        $ticket = $token->getCredentials();
        $uid = $this->simplecas->validateTicket($ticket);

        if ($uid === false) {
            throw new BadCredentialsException(sprintf('Invalid CAS ticket: %s', $ticket));
        }

        $user = $this->userProvider->loadUserByUsername($user);
        $this->accountChecker->checkPostAuth($user);

        return new SimpleCASToken($user, $token->getCredentials(), $user->getRoles());
    }

    /**
     * Checks whether this provider supports the given token.
     *
     * @param TokenInterface $token A TokenInterface instance
     * @return boolean true if the implementation supports the Token, false otherwise
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof SimpleCASToken;
    }
}
