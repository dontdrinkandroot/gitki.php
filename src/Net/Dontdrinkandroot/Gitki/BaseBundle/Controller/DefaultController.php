<?php

namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        var_dump($this->getUser());
        return $this->render('DdrGitkiBaseBundle:Default:index.html.twig');
    }
}
