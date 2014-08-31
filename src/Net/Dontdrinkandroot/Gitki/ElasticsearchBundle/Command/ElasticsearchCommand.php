<?php


namespace Net\Dontdrinkandroot\Gitki\ElasticsearchBundle\Command;

use Net\Dontdrinkandroot\Gitki\BaseBundle\Command\GitkiContainerAwareCommand;
use Net\Dontdrinkandroot\Gitki\ElasticsearchBundle\Repository\ElasticsearchRepository;

abstract class ElasticsearchCommand extends GitkiContainerAwareCommand
{

    /**
     * @return ElasticsearchRepository
     */
    protected function getElasticsearchRepository()
    {
        return $this->getContainer()->get('ddr.gitki.repository.elasticsearch');
    }
}