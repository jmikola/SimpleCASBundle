<?php

namespace Bundle\SimpleCASBundle\Controller;

use Symfony\Framework\WebBundle\Controller;

class AuthController extends Controller
{
    public function loginAction()
    {
        $this->container->getCasService()->forceAuthentication();
        
        if ($this->container->getParameter('simplecas.login_redirect_route')) {
            return $this->redirect($this->generateUrl($this->container->getParameter('simplecas.login_redirect_route'), array(), true));
        } elseif ($this->container->getParameter('simplecas.login_redirect_url')) {
            return $this->redirect($this->container->getParameter('simplecas.login_redirect_url'));
        }
        
        $parameters = array('username' => $this->container->getCasService()->getUsername());
        
        return $this->render('SimpleCASBundle:Auth:login:php', $parameters);
    }

    public function logoutAction()
    {
        $this->container->getCasService()->logout();
    }
}
