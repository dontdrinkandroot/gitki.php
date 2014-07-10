<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Controller;


use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Service\WikiService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BaseController extends Controller
{

    /**
     * @return WikiService
     */
    protected function getWikiService()
    {
        return $this->get('ddr.gitki.service.wiki');
    }

    /**
     * @return MarkdownParserInterface
     */
    protected function getMarkdownParser()
    {
        return $this->get('markdown.parser');
    }

} 