<?php


namespace Dontdrinkandroot\Gitki\ElasticsearchBundle\Command;

use Dontdrinkandroot\Gitki\BaseBundle\Command\GitkiContainerAwareCommand;
use Dontdrinkandroot\Gitki\ElasticsearchBundle\Repository\ElasticsearchRepository;

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