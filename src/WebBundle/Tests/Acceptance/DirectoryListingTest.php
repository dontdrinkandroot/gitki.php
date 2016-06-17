<?php


namespace Dontdrinkandroot\Gitki\WebBundle\Tests\Acceptance;

use Dontdrinkandroot\Gitki\WebBundle\DataFixtures\ORM\UserReferenceTrait;
use Dontdrinkandroot\Gitki\WebBundle\DataFixtures\ORM\Users;

class DirectoryListingTest extends BaseAcceptanceTest
{

    use UserReferenceTrait;

    /**
     * {@inheritdoc}
     */
    protected function getFixtureClasses()
    {
        return [Users::class];
    }

    public function testDirectoryIndex()
    {
        $this->logIn($this->getUser(Users::COMMITTER));

        $crawler = $this->client->request('GET', '/browse/?action=list');
        $this->assertStatusCode(200, $this->client);

        $panels = $crawler->filter('.panel');
        $this->assertCount(2, $panels);

        $this->assertEquals('Folders', $panels->eq(0)->filter('.panel-title')->text());
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
