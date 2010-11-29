<?php

namespace Bundle\SimpleCASBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

abstract class AuthController extends Controller
{
    /**
     * Returns the absolute service URL that CAS should redirect to after
     * logging out.  This will also be used for redirection after logging in,
     * if a referer is not available.
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

        /* If the user is attempting to log in while already authenticated,
         * assume they wish to reauthenticate as another user.  Redirect the
         * user to the CAS logout URL, which should return to this login action.
         *
         * If a referer URL is available, it will be saved for post-login
         * redirection.
         */
        if ($simplecas->isAuthenticated()) {
            $simplecas->unauthenticate();

            if ($referer = $this->getRefererUrl()) {
                $simplecas->setLoginRedirectUrl($referer);
            }

            return $this->redirect($simplecas->getLogoutUrl());
        }

        return $this->redirect($simplecas->getLoginUrl($this->getLoginRedirectUrlOnce()));
    }

    public function logoutAction()
    {
        $simplecas = $this->getSimpleCAS();
        $simplecas->unauthenticate();
        return $this->redirect($simplecas->getLogoutUrl($this->getServiceUrl()));
    }

    /**
     * Get the post-login redirect URL.
     *
     * If no redirect URL is saved in the session, this will default to the
     * referer.  If either of those URL's is invalid (i.e. an internal CAS URL),
     * the service URL will be returned.
     *
     * @see isValidRedirectUrl()
     * @return string
     */
    protected function getLoginRedirectUrl()
    {
        $loginRedirectUrl = $this->getSimpleCAS()->getLoginRedirectUrl($this->getRefererUrl());

        // Default to service URL if the redirect URL is empty or invalid
        if (! ($loginRedirectUrl && $this->isValidRedirectUrl($loginRedirectUrl))) {
            $loginRedirectUrl = $this->getServiceUrl();
        }

        return $loginRedirectUrl;
    }

    /**
     * Get the post-login redirect URL and ensure it's removed from the session.
     *
     * @return string
     */
    protected function getLoginRedirectUrlOnce()
    {
        $loginRedirectUrl = $this->getLoginRedirectUrl();
        $this->getSimpleCAS()->removeLoginRedirectUrl();

        return $loginRedirectUrl;
    }

    /**
     * Set the post-login redirect URL.
     *
     * @param string
     */
    protected function setLoginRedirectUrl($loginRedirectUrl)
    {
        $this->getSimpleCAS()->setLoginRedirectUrl($loginRedirectUrl);
    }

    /**
     * Get the request referer URL.
     *
     * @return string
     */
    protected function getRefererUrl()
    {
        return $this->getRequest()->headers->get('referer');
    }

    /**
     * Check that the URL parameter does not point to the login action or one of
     * the CAS login/logout URL's (sans query string).
     *
     * @param string $url
     * @return boolean
     */
    protected function isValidRedirectUrl($url)
    {
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
     * @return Symfony\Component\HttpFoundation\Session
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
