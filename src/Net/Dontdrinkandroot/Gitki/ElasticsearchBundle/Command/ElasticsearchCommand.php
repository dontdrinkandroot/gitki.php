<?php


namespace Net\Dontdrinkandroot\Gitki\ElasticsearchBundle\Command;


use Net\Dontdrinkandroot\Gitki\BaseBundle\Service\MarkdownService;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Service\WikiService;
use Net\Dontdrinkandroot\Gitki\ElasticsearchBundle\Repository\ElasticsearchRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class ElasticsearchCommand extends ContainerAwareCommand
{

    /**
     * @return ElasticsearchRepository
     */
    protected function getElasticsearchRepository()
    {
        return $this->getContainer()->get('ddr.gitki.repository.elasticsearch');
    }

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