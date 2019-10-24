<?php

namespace App\Tests\Acceptance;

use App\Entity\User;
use App\Tests\Integration\BaseIntegrationTest;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

abstract class BaseAcceptanceTest extends BaseIntegrationTest
{
    /** @var KernelBrowser */
    protected $client;

    /** @var ReferenceRepository; */
    protected $referenceRepository;

    protected function loadClientAndFixtures(array $classNames = [], bool $catchExceptions = true): ReferenceRepository
    {
        $this->client = self::createClient();
        $this->client->catchExceptions($catchExceptions);
        $this->referenceRepository = $this->loadFixtures($classNames)->getReferenceRepository();

        return $this->referenceRepository;
    }

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
}
