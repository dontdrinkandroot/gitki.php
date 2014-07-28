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
                ->scalarNode('repository_path')->isRequired()->end()
                ->scalarNode('name')->defaultValue('GitKi')->end()
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
                ->arrayNode('twig')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('show_breadcrumbs')->defaultTrue()->end()
                        ->booleanNode('show_toc')->defaultTrue()->end()
                    ->end()
                ->end()
            ->end();
        // @formatter:on

        $this->postProcessRootNode($rootNode);

        return $treeBuilder;
    }

    protected function postProcessRootNode($rootNode)
    {
    }
}
