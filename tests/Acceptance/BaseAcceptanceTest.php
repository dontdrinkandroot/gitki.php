<?php

namespace App\Tests\Acceptance;

use App\Entity\User;
use App\Tests\Integration\BaseIntegrationTest;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

abstract class BaseAcceptanceTest extends BaseIntegrationTest
{
    /**
     * @param User $user
     */
    protected function logIn(AbstractBrowser $client, User $user)
    {
        $session = $client->getContainer()->get('session');

        $firewall = 'main';
        $token = new UsernamePasswordToken($user, null, $firewall, $user->getRoles());
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }

    protected function logOut(AbstractBrowser $client)
    {
        $session = $client->getContainer()->get('session');
        $session->clear();
        $session->save();

        $client->getCookieJar()->clear();
    }
}
