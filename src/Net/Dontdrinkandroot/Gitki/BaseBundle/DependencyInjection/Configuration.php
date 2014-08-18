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
                ->arrayNode('users')
                    ->prototype('array')
                        ->children()
                            ->integerNode('id')->isRequired()->end()
                            ->scalarNode('real_name')->end()
                            ->scalarNode('github_login')->end()
                            ->scalarNode('google_login')->end()
                            ->scalarNode('primary_email_address')->end()
                            ->arrayNode('roles')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
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
