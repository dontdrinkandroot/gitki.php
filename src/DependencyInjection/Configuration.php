<?php

namespace App\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('ddr_gitki_app');
        $rootNode = $treeBuilder->getRootNode();

        // @formatter:off
        $rootNode->children()
                ->scalarNode('display_name')->end()
                ->scalarNode('repository_path')->end()
                ->scalarNode('database_url')->end()
                ->arrayNode('elasticsearch')
                    ->children()
                        ->booleanNode('enabled')->end()
                        ->scalarNode('index_name')->end()
                        ->scalarNode('host')->end()
                        ->integerNode('port')->end()
                    ->end()
                ->end()
        ->end();
        // @formatter:on

        return $treeBuilder;
    }
}
