<?php

namespace Dontdrinkandroot\Gitki\WebBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ddr_gitki_web');

        // @formatter:off
        $rootNode
            ->children()
                ->arrayNode('authentication')
                    ->children()
                        ->booleanNode('form_login_enabled')->defaultTrue()->end()
                        ->arrayNode('oauth')
                            ->children()
                                ->scalarNode('default_provider')->end()
                                ->arrayNode('providers')
                                    ->prototype('array')
                                        ->children()
                                            ->scalarNode('client_id')->isRequired()->end()
                                            ->scalarNode('secret')->isRequired()->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
        // @formatter:on

        return $treeBuilder;
    }
}
