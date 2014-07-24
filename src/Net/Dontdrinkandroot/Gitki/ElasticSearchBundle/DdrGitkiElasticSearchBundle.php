<?php

namespace Net\Dontdrinkandroot\Gitki\ElasticSearchBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class DdrGitkiElasticSearchBundle extends Bundle
{

    public function getParent()
    {
        return 'DdrGitkiBaseBundle';
    }
}
