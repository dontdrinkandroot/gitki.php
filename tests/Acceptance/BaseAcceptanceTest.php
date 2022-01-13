<?php

namespace App\Tests\Acceptance;

use App\Entity\User;
use App\Tests\Integration\BaseIntegrationTest;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

abstract class BaseAcceptanceTest extends BaseIntegrationTest
{
    protected KernelBrowser $client;

    protected ReferenceRepository $referenceRepository;

    protected function loadClientAndFixtures(array $classNames = [], bool $catchExceptions = true): ReferenceRepository
    {
        self::ensureKernelShutdown();
        $this->client = self::createClient();
        $this->client->catchExceptions($catchExceptions);
        $this->referenceRepository = self::getContainer()
            ->get(DatabaseToolCollection::class)->get()
            ->loadFixtures($classNames)->getReferenceRepository();

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
