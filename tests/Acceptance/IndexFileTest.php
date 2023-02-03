<?php

namespace App\Tests\Acceptance;

use App\DataFixtures\UserCommitter;
use App\DataFixtures\UserReferenceTrait;
use App\DataFixtures\Users;

class IndexFileTest extends BaseAcceptanceTest
{
    use UserReferenceTrait;

    /**
     * {@inheritdoc}
     */
    protected function getFixtureClasses(): array
    {
        return [Users::class];
    }

    public function testIndexFileShown(): void
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class]);

        $this->login($this->getUser(UserCommitter::class, $referenceRepository));

        $crawler = $this->client->request('GET', '/');
//        $this->assertStatusCode(302, $client);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());

        $location = $this->client->getResponse()->headers->get('Location');
        $this->assertEquals('/browse/?action=index', $location);
        $crawler = $this->client->request('GET', $location);
//        $this->assertStatusCode(302, $client);

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $location = $this->client->getResponse()->headers->get('Location');
        $this->assertEquals('/browse/index.md', $location);
        $crawler = $this->client->request('GET', $location);
//        $this->assertStatusCode(200, $client);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('Welcome', $crawler->filter('h1')->text());
    }
}
