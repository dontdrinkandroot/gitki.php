<?php

namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends BaseController
{
    public function indexAction()
    {
        return $this->render('DdrGitkiBaseBundle:Default:index.html.twig');
    }
}
