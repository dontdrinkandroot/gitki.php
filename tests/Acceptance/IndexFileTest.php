<?php

namespace App\Tests\Acceptance;

use App\DataFixtures\UserReferenceTrait;
use App\DataFixtures\Users;

class IndexFileTest extends BaseAcceptanceTest
{
    use UserReferenceTrait;

    /**
     * {@inheritdoc}
     */
    protected function getFixtureClasses()
    {
        return [Users::class];
    }

    public function testIndexFileShown()
    {
        $referenceRepository = $this->loadFixtures([Users::class])->getReferenceRepository();
        $client = $this->makeBrowser();

        $this->login($client, $this->getUser(Users::COMMITTER, $referenceRepository));

        $crawler = $client->request('GET', '/');
//        $this->assertStatusCode(302, $client);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $location = $client->getResponse()->headers->get('Location');
        $this->assertEquals('/browse/?action=index', $location);
        $crawler = $client->request('GET', $location);
//        $this->assertStatusCode(302, $client);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $location = $client->getResponse()->headers->get('Location');
        $this->assertEquals('/browse/index.md', $location);
        $crawler = $client->request('GET', $location);
//        $this->assertStatusCode(200, $client);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('Welcome', $crawler->filter('h1')->text());
    }
}
