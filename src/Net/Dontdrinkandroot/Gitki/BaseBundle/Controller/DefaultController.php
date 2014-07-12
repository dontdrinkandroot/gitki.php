<?php

namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Controller;

class DefaultController extends BaseController
{
    public function indexAction()
    {
        return $this->redirect($this->generateUrl('ddr_gitki_wiki_page', array('path' => 'index.md')));
        //return $this->render('DdrGitkiBaseBundle:Default:index.html.twig');
    }
}
