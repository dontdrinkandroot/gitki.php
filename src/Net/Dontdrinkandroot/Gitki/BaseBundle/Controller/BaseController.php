<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Controller;


use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Service\WikiService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

    protected function assertRole($role)
    {
        if (false === $this->get('security.context')->isGranted($role)) {
            throw new AccessDeniedException();
        }
    }

} 