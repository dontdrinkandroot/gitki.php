<?php

namespace Dontdrinkandroot\Gitki\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('DdrGitkiWebBundle:Default:index.html.twig');
    }
}
