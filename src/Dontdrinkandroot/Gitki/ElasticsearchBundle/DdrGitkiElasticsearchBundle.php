<?php

namespace Dontdrinkandroot\Gitki\ElasticsearchBundle;

use Dontdrinkandroot\Gitki\ElasticsearchBundle\DependencyInjection\DdrGitkiExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DdrGitkiElasticsearchBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->registerExtension(new DdrGitkiExtension());
    }

    public function getParent()
    {
        return 'DdrGitkiBaseBundle';
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
