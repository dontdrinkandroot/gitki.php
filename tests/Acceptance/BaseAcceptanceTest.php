<?php

namespace App\Tests\Acceptance;

use App\DataFixtures\ProxiedLoader;
use App\Entity\User;
use App\Tests\Integration\BaseIntegrationTest;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

abstract class BaseAcceptanceTest extends BaseIntegrationTest
{
    /**
     * @var Client
     */
    protected $client = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    /**
     * @param User $user
     */
    protected function logIn(User $user)
    {
        $session = $this->client->getContainer()->get('session');

        $firewall = 'main';
        $token = new UsernamePasswordToken($user, null, $firewall, $user->getRoles());
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    protected function logOut()
    {
        $session = $this->client->getContainer()->get('session');
        $session->clear();
        $session->save();

        $this->client->getCookieJar()->clear();
    }

    protected function getFixtureLoader(ContainerInterface $container, array $classNames)
    {
        $container = $this->getContainer();
        $loader = new ProxiedLoader($container->get('test.doctrine.fixtures.loader'));
        foreach ($classNames as $fixtureClass) {
            $loader->addByClass($fixtureClass);
        }

        return $loader;
    }
}
