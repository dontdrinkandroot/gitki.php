<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Loader\SymfonyFixturesLoader;
use Doctrine\Common\DataFixtures\Loader;

class ProxiedLoader extends Loader
{
    /**
     * @var SymfonyFixturesLoader
     */
    private $proxyLoader;

    public function __construct(SymfonyFixturesLoader $proxyLoader)
    {
        $this->proxyLoader = $proxyLoader;
    }

    public function addByClass(string $fixtureClass)
    {
        $this->addFixture($this->proxyLoader->getFixture($fixtureClass));
    }

    protected function createFixture($class)
    {
        return $this->proxyLoader->getFixture($class);
    }
}
