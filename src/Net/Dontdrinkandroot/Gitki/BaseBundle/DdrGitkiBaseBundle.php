<?php

namespace Net\Dontdrinkandroot\Gitki\BaseBundle;

use Net\Dontdrinkandroot\Gitki\BaseBundle\DependencyInjection\DdrGitkiExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DdrGitkiBaseBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->registerExtension(new DdrGitkiExtension());
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
