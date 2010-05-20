<?php

namespace Bundle\SimpleCASBundle\Controller;

use Symfony\Framework\WebBundle\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('SimpleCASBundle:Default:index');
    }
}
