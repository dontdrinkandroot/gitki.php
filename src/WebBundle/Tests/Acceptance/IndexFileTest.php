<?php


namespace Dontdrinkandroot\Gitki\WebBundle\Tests\Acceptance;

class IndexFileTest extends BaseAcceptanceTest
{

    /**
     * {@inheritdoc}
     */
    protected function getFixtureClasses()
    {
        return [];
    }

    public function testIndexFileShown()
    {
        $crawler = $this->client->request('GET', '/');
        $this->assertStatusCode(302, $this->client);

        $location = $this->client->getResponse()->headers->get('Location');
        $this->assertEquals('/browse/?action=index', $location);
        $crawler = $this->client->request('GET', $location);
        $this->assertStatusCode(302, $this->client);

        $location = $this->client->getResponse()->headers->get('Location');
        $this->assertEquals('/browse/index.md', $location);
        $crawler = $this->client->request('GET', $location);
        $this->assertStatusCode(200, $this->client);

        $this->assertEquals('Welcome', $crawler->filter('h1')->text());
    }
}
