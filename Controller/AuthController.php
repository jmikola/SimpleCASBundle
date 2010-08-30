<?php

namespace Bundle\SimpleCASBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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

        // TODO: Refactor to use a single getRedirectUrl method
        $redirectUrl = $session->get(self::REFERER, $requestHeaders->get('referer'));
        $session->remove(self::REFERER);

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
        return $this->redirect($simplecas->getLogoutUrl($this->getUrl()));
    }

    /**
     * Check that the URL parameter does not point to the login action or one of
     * the CAS login/logout URL's (sans query string).
     *
     * @param string $url
     * @return boolean
     */
    protected function isValidRedirectUrl($url) {
        $invalidUrls = array(
        $this->getLoginActionUrl(),
        preg_replace('/\?.*$/', '', $this->getSimpleCAS()->getLoginUrl()),
        preg_replace('/\?.*$/', '', $this->getSimpleCAS()->getLogoutUrl()),
        );

        foreach ($invalidUrls as $invalidUrl) {
            if (0 === strncmp($url, $invalidUrl, strlen($invalidUrl))) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function getRequest()
    {
        return $this['request'];
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session
     */
    protected function getSession()
    {
        return $this['session'];
    }

    /**
     * @return \Bundle\SimpleCASBundle\SimpleCAS
     */
    protected function getSimpleCAS()
    {
        return $this['simplecas'];
    }
}
