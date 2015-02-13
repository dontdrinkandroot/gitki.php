<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Controller;

class AboutController extends BaseController
{

    public function markdownSyntaxAction()
    {
        return $this->render('DdrGitkiBaseBundle:About:markdownsyntax.html.twig');
    }
}
