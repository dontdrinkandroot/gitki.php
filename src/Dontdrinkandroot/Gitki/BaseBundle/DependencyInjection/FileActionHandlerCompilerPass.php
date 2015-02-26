<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FileActionHandlerCompilerPass implements CompilerPassInterface
{

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ddr.gitki.service.action_handler.file')) {
            return;
        }

        $handlerServiceDefinition = $container->getDefinition('ddr.gitki.service.action_handler.file');

        $taggedServices = $container->findTaggedServiceIds('ddr.gitki.action_handler.file');

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $action = '';
                if (isset($attributes['action'])) {
                    $action = $attributes['action'];
                }
                $extension = '';
                if (isset($attributes['extension'])) {
                    $extension = $attributes['extension'];
                }
                $handlerServiceDefinition->addMethodCall(
                    'registerHandler',
                    [new Reference($id), $action, $extension]
                );
            }
        }
    }
}
