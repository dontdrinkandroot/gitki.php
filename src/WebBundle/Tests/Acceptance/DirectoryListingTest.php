<?php


namespace Dontdrinkandroot\Gitki\WebBundle\Tests\Acceptance;

class DirectoryListingTest extends BaseAcceptanceTest
{

    /**
     * {@inheritdoc}
     */
    protected function getFixtureClasses()
    {
        return [];
    }

    public function testDirectoryIndex()
    {
        $crawler = $this->client->request('GET', '/browse/?action=list');
        $this->assertStatusCode(200, $this->client);

        $panels = $crawler->filter('.panel');
        $this->assertCount(2, $panels);

        $this->assertEquals('Directories', $panels->eq(0)->filter('.panel-title')->text());
        $this->assertCount(1, $panels->eq(0)->filter('.table tr'));
        $this->assertEquals(
            'Examples',
            $panels->eq(0)->filter('.table tr')->eq(0)->filter('td')->eq(1)->filter('a')->text()
        );

        $this->assertEquals('Files', $panels->eq(1)->filter('.panel-title')->text());
        $this->assertCount(1, $panels->eq(1)->filter('.table tr'));
        $this->assertEquals(
            'index.md',
            trim($panels->eq(1)->filter('.table tr')->eq(0)->filter('td')->eq(1)->filter('a')->text())
        );
    }
}
