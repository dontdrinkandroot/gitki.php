<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Command;

use Dontdrinkandroot\Gitki\BaseBundle\Repository\ElasticsearchRepository;

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
