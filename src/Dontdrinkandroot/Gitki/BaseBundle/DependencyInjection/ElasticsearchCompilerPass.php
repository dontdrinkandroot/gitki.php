<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ElasticsearchCompilerPass implements CompilerPassInterface
{

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $enabled = $container->getParameter('ddr_gitki.elasticsearch.enabled');
        if (!$enabled) {
            return;
        }

        $repositoryDefinition = $container->findDefinition('ddr.gitki.repository.elasticsearch');
        $repositoryDefinition->setClass('Dontdrinkandroot\Gitki\BaseBundle\Repository\ElasticsearchRepository');
        $repositoryDefinition->setArguments(
            [
                $container->getParameter('ddr_gitki.elasticsearch.host'),
                $container->getParameter('ddr_gitki.elasticsearch.port'),
                $container->getParameter('ddr_gitki.elasticsearch.index_name')
            ]
        );

        $taggedServices = $container->findTaggedServiceIds('ddr.gitki.analyzer');

        foreach ($taggedServices as $id => $tags) {
            $repositoryDefinition->addMethodCall(
                'registerAnalyzer',
                [new Reference($id)]
            );
        }
    }
}
