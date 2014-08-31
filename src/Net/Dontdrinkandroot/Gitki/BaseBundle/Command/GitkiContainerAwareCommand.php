<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Command;

use Net\Dontdrinkandroot\Gitki\BaseBundle\Service\Markdown\MarkdownService;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Service\WikiService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class GitkiContainerAwareCommand extends ContainerAwareCommand
{

    /**
     * @return WikiService
     */
    protected function getWikiService()
    {
        return $this->getContainer()->get('ddr.gitki.service.wiki');
    }

    /**
     * @return MarkdownService
     */
    protected function getMarkdownService()
    {
        return $this->getContainer()->get('ddr.gitki.service.markdown');
    }
}