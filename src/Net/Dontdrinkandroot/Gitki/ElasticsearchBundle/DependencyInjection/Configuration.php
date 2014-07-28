<?php

namespace Net\Dontdrinkandroot\Gitki\ElasticsearchBundle\DependencyInjection;

use Net\Dontdrinkandroot\Gitki\BaseBundle\DependencyInjection\Configuration as BaseConfigruation;


class Configuration extends BaseConfigruation
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        return parent::getConfigTreeBuilder();
    }

    protected function postProcessRootNode($rootNode)
    {
        $rootNode->children()
            ->arrayNode('elasticsearch')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('host')->defaultValue('localhost')->end()
            ->integerNode('port')->defaultValue(9200)->end()
            ->end()
            ->end()
            ->end();
    }

}
