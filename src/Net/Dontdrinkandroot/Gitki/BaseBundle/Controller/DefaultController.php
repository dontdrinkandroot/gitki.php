<?php

namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Controller;

class DefaultController extends BaseController
{
    public function indexAction()
    {
        return $this->redirect($this->generateUrl('ddr_gitki_wiki_file', array('path' => '/index.md')));
    }

    public function loggedoutAction()
    {
        return $this->render('DdrGitkiBaseBundle:Default:loggedout.html.twig');
    }
}
