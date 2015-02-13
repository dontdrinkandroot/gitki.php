<?php

namespace Dontdrinkandroot\Gitki\ElasticsearchBundle\DependencyInjection;

use Dontdrinkandroot\Gitki\BaseBundle\DependencyInjection\Configuration as BaseConfigruation;

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
        // @formatter:off
        $rootNode->children()
            ->arrayNode('elasticsearch')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('index_name')->isRequired()->end()
                    ->scalarNode('host')->defaultValue('localhost')->end()
                    ->integerNode('port')->defaultValue(9200)->end()
                ->end()
            ->end()
        ->end();
        // @formatter:on
    }
}
