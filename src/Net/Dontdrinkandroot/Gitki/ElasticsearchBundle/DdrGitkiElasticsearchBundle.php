<?php

namespace Net\Dontdrinkandroot\Gitki\ElasticsearchBundle;

use Net\Dontdrinkandroot\Gitki\ElasticsearchBundle\DependencyInjection\DdrGitkiExtension;
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
}
