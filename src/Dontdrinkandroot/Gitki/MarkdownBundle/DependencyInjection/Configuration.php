<?php

namespace Dontdrinkandroot\Gitki\MarkdownBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('ddr_gitki_markdown');

        // @formatter:off
        $rootNode
            ->children()
                ->booleanNode('allow_html')->defaultFalse()->end()
            ->end();
        // @formatter:on

        return $treeBuilder;
    }
}
