<?php

namespace Net\Dontdrinkandroot\Gitki\ElasticSearchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;


class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ddr_gitki_elastic_search');

        $rootNode->children()
            ->scalarNode('host')->defaultValue('localhost')->end()
            ->integerNode('port')->defaultValue(9200)->end()
            ->end();

        return $treeBuilder;
    }
}
