<?php

namespace Bundle\SimpleCASBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller;

abstract class AuthController extends Controller
{
    /**
     * Session attribute for stashing the login action's referrer
     */
    const REFERER = '__SIMPLECAS_LOGIN_REFERER';

    public function loginAction()
    {
        $simplecas = $this->container->get('simplecas');

        /* If the user is attempting to log in while already authenticated,
         * assume they wish to reauthenticate as another user.  Redirect the
         * user to the CAS logout URL, which should return to this login action.
         *
         * The current referer will be saved if it is available.
         */
        if ($simplecas->isAuthenticated()) {
            $simplecas->unauthenticate();

            if ($referer = $this->getRequest()->headers->get('referer')) {
                $this->getUser()->setAttribute(self::REFERER, $referer);
            }

            return $this->redirect($simplecas->getLogoutUrl());
        }

        // Use the default service URL if a refererr is not available
        $redirectUrl = $this->getUser()->removeAttribute(self::REFERER) ?:
                       $this->getRequest()->headers->get('referer', $this->getUrl());

        return $this->redirect($simplecas->getLoginUrl($redirectUrl));
    }

    public function logoutAction()
    {
        $simplecas = $this->container->get('simplecas');
        $simplecas->unauthenticate();
        return $this->redirect($simplecas->getLogoutUrl($this->getUrl()));
    }

	/**
	 * @return \Symfony\Components\HttpFoundation\Request
	 */
	protected function getRequest() {
		return $this->container->get('request');
	}

    /**
     * Returns the service URL that CAS should redirect to after logging out.
     * This will also be used for logging in, if a referer is not available.
     *
     * @return string
     */
    abstract protected function getUrl();
}
