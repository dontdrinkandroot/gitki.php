<?php

namespace Dontdrinkandroot\Gitki\BaseBundle;

use Dontdrinkandroot\Gitki\BaseBundle\DependencyInjection\DdrGitkiExtension;
use Dontdrinkandroot\Gitki\BaseBundle\DependencyInjection\DirectoryActionHandlerCompilerPass;
use Dontdrinkandroot\Gitki\BaseBundle\DependencyInjection\ElasticsearchCompilerPass;
use Dontdrinkandroot\Gitki\BaseBundle\DependencyInjection\FileActionHandlerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DdrGitkiBaseBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->registerExtension(new DdrGitkiExtension());
        $container->addCompilerPass(new DirectoryActionHandlerCompilerPass());
        $container->addCompilerPass(new FileActionHandlerCompilerPass());
        $container->addCompilerPass(new ElasticsearchCompilerPass());
    }

    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new DdrGitkiExtension();
        }

        if ($this->extension) {
            return $this->extension;
        }

        return false;
    }
}
