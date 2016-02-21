<?php


namespace Dontdrinkandroot\Gitki\WebBundle\Tests\Acceptance;

use Dontdrinkandroot\Gitki\WebBundle\DataFixtures\ORM\Users;

class DirectoryListingTest extends BaseAcceptanceTest
{

    /**
     * {@inheritdoc}
     */
    protected function getFixtureClasses()
    {
        return [Users::class];
    }

    public function testBla()
    {
        $this->assertTrue(true);
    }
}
