<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DirectoryActionHandlerCompilerPass implements CompilerPassInterface
{

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ddr.gitki.service.action_handler.directory')) {
            return;
        }

        $definition = $container->getDefinition(
            'ddr.gitki.service.action_handler.directory'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'ddr.gitki.action_handler.directory'
        );
        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $action = '';
                if (isset($attributes['action'])) {
                    $action = $attributes['action'];
                }
                $definition->addMethodCall(
                    'registerHandler',
                    [new Reference($id), $action]
                );
            }
        }
    }
}
