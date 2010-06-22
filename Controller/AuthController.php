<?php

namespace Bundle\SimpleCASBundle\Controller;

use Symfony\Framework\WebBundle\Controller;

class AuthController extends Controller
{
    public function loginAction()
    {
        $parameters = array('uid' => $this->container->getCasService()->requireLogin()->getAuthenticatedUid());
        return $this->render('SimpleCASBundle:Auth:login', $parameters);
    }

    public function logoutAction()
    {
        $this->container->getCasService()->requireLogout();
        return $this->render('SimpleCASBundle:Auth:logout');
    }
}
