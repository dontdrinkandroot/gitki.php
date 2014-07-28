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
}
