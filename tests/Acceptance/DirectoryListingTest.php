<?php

namespace App\Tests\Acceptance;

use App\DataFixtures\UserReferenceTrait;
use App\DataFixtures\Users;

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

        $firstPanel = $panels->eq(0);
        $this->assertEquals('Folders', $firstPanel->filter('.panel-title')->text());
        $listGroupItems = $firstPanel->filter('.list-group-item');
        $this->assertCount(1, $listGroupItems);
        $this->assertEquals(
            'Examples',
            $listGroupItems->eq(0)->filter('.ddr-gitki-name a')->text()
        );

        $secondPanel = $panels->eq(1);
        $this->assertEquals('Files', $secondPanel->filter('.panel-title')->text());
        $listGroupItems = $secondPanel->filter('.list-group-item');
        $this->assertCount(1, $secondPanel->filter('.list-group-item'));
        $this->assertEquals(
            'index.md',
            trim($listGroupItems->eq(0)->filter('.ddr-gitki-name a')->text())
        );
    }
}
