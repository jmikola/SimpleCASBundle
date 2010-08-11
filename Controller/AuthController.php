<?php

namespace Bundle\SimpleCASBundle\Controller;

use Symfony\Framework\WebBundle\Controller;

abstract class AuthController extends Controller
{
    /**
     * Session attribute for stashing the login action's referrer
     */
    const REFERER = '__SIMPLECAS_LOGIN_REFERER';

    /**
     * Returns the absolute service URL that CAS should redirect to after
     * logging out.  This will also be used for logging in, if a referer is not
     * available.
     *
     * @return string
     */
    abstract protected function getServiceUrl();

    /**
     * Returns the absolute URL to the login action, which is needed by the
     * login action to ensure it never redirects to itself.
     *
     * @return string
     */
    abstract protected function getLoginActionUrl();

    public function loginAction()
    {
        $simplecas = $this->getSimpleCAS();
        $session = $this->getUser();
        $requestHeaders = $this->getRequest()->headers;

        /* If the user is attempting to log in while already authenticated,
         * assume they wish to reauthenticate as another user.  Redirect the
         * user to the CAS logout URL, which should return to this login action.
         *
         * The current referer will be saved if it is available.
         */
        if ($simplecas->isAuthenticated()) {
            $simplecas->unauthenticate();

            if ($referer = $requestHeaders->get('referer')) {
                $session->setAttribute(self::REFERER, $referer);
            }

            return $this->redirect($simplecas->getLogoutUrl());
        }

        $redirectUrl = $session->removeAttribute(self::REFERER) ?: $requestHeaders->get('referer');

        // Default to service URL if the referrer is invalid
        if (! $this->isValidRedirectUrl($redirectUrl)) {
            $redirectUrl = $this->getServiceUrl();
        }

        return $this->redirect($simplecas->getLoginUrl($redirectUrl));
    }

    public function logoutAction()
    {
        $simplecas = $this->getSimpleCAS();
        $simplecas->unauthenticate();
        return $this->redirect($simplecas->getLogoutUrl($this->getServiceUrl()));
    }

    /**
     * Check that the URL parameter does not point to the login action or one of
     * the CAS login/logout URL's.
     *
     * @param string $url
     * @return boolean
     */
    protected function isValidRedirectUrl($url) {
        $parsedUrl = parse_url($url);

        $invalidUrls = array(
            $this->getLoginActionUrl(),
            $this->getSimpleCAS()->getLoginUrl(),
            $this->getSimpleCAS()->getLogoutUrl(),
        );

        foreach ($invalidUrls as $invalidUrl) {
            // TODO: Ensure candidate URL does not match host/path of any invalid URL
        }

        return true;
    }

    /**
     * @return \Bundle\SimpleCASBundle\SimpleCAS
     */
    protected function getSimpleCAS() {
        return $this->container->getService('simplecas');
    }
}
