<?php

namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Controller;

class DefaultController extends BaseController
{
    public function indexAction()
    {
        $history = $this->getWikiService()->getHistory(20);

        return $this->render('DdrGitkiBaseBundle:Default:index.html.twig', array('history' => $history));
    }

    public function loggedoutAction()
    {
        return $this->render('DdrGitkiBaseBundle:Default:loggedout.html.twig');
    }
}
