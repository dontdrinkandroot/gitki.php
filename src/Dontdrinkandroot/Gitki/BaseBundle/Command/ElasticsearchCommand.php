<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Command;

use Dontdrinkandroot\Gitki\BaseBundle\Repository\ElasticsearchRepositoryInterface;

abstract class ElasticsearchCommand extends GitkiContainerAwareCommand
{

    /**
     * @return ElasticsearchRepositoryInterface
     */
    protected function getElasticsearchRepository()
    {
        return $this->getContainer()->get('ddr.gitki.repository.elasticsearch');
    }
}
