<?php

namespace App\Tests\Acceptance;

use App\DataFixtures\UserCommitter;
use App\DataFixtures\UserReferenceTrait;
use App\DataFixtures\Users;

class DirectoryListingTest extends BaseAcceptanceTest
{
    use UserReferenceTrait;

    public function testDirectoryIndex(): void
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class]);

        $this->logIn($this->getUser(UserCommitter::class, $referenceRepository));

        $crawler = $this->client->request('GET', '/browse/?action=list');
        //$this->assertStatusCode(200, $client);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $cards = $crawler->filter('.card');
        $this->assertCount(2, $cards);

        $firstCard = $cards->eq(0);
        $this->assertEquals('Folders', $firstCard->filter('.card-header')->text());
        $listGroupItems = $firstCard->filter('.list-group-item');
        $this->assertCount(1, $listGroupItems);
        $this->assertEquals(
            'Examples',
            $listGroupItems->eq(0)->filter('.ddr-gitki-item-name a')->text()
        );

        $secondCard = $cards->eq(1);
        $this->assertEquals('Files', $secondCard->filter('.card-header')->text());
        $listGroupItems = $secondCard->filter('.list-group-item');
        $this->assertCount(1, $secondCard->filter('.list-group-item'));
        $this->assertEquals(
            'index.md',
            trim($listGroupItems->eq(0)->filter('.ddr-gitki-item-name a')->text())
        );
    }
}
