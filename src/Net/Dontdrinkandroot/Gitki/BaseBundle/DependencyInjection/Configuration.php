<?php

namespace Net\Dontdrinkandroot\Gitki\BaseBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('ddr_gitki');

        // @formatter:off
        $rootNode
            ->children()
                ->arrayNode('oauth')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('client_id')->isRequired()->end()
                            ->scalarNode('secret')->isRequired()->end()
                            ->arrayNode('users_admin')
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('users_commit')
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('users_watch')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
        // @formatter:on

        return $treeBuilder;
    }
}
