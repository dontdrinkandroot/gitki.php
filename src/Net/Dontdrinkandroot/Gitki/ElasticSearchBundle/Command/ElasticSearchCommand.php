<?php


namespace Net\Dontdrinkandroot\Gitki\ElasticSearchBundle\Command;


use Net\Dontdrinkandroot\Gitki\BaseBundle\Service\WikiService;
use Net\Dontdrinkandroot\Gitki\ElasticSearchBundle\Repository\ElasticSearchRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class ElasticSearchCommand extends ContainerAwareCommand
{

    /**
     * @return ElasticSearchRepository
     */
    protected function getElasticSearchRepository()
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
} 