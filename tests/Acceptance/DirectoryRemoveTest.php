<?php

namespace App\Tests\Acceptance;

use App\DataFixtures\UserAdmin;
use App\DataFixtures\UserCommitter;
use App\DataFixtures\UserReferenceTrait;
use App\DataFixtures\Users;
use Dontdrinkandroot\GitkiBundle\Service\FileSystem\FileSystemService;
use Dontdrinkandroot\GitkiBundle\Service\Wiki\WikiService;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;

class DirectoryRemoveTest extends BaseAcceptanceTest
{
    use UserReferenceTrait;

    public function testRemoveEmptyDirectoryTest(): void
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class]);

        $directoryPath = DirectoryPath::parse('/testdirectory/');
        /** @var FileSystemService $fileSystemService */
        $fileSystemService = self::getContainer()->get(FileSystemService::class);
        /** @var WikiService $wikiService */
        $wikiService = self::getContainer()->get(WikiService::class);
        $wikiService->createFolder($directoryPath);
        $this->assertFileExists(
            $directoryPath->prepend($fileSystemService->getBasePath())->toAbsoluteFileSystemString()
        );

        $this->login($this->getUser(UserCommitter::class, $referenceRepository));
        $this->client->request('GET', '/browse/testdirectory/?action=remove');
//        $this->assertStatusCode(302, $client);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('/browse/', $this->client->getResponse()->headers->get('Location'));

        $this->assertFileDoesNotExist(
            $directoryPath->prepend($fileSystemService->getBasePath())->toAbsoluteFileSystemString()
        );
    }

    public function testRemoveNonEmptyDirectoryTest(): void
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class]);

        /** @var FileSystemService $fileSystemService */
        $fileSystemService = $this->getContainer()->get(FileSystemService::class);

        $exampleFile = FilePath::parse('/examples/toc-example.md');
        $exampleDirectory = DirectoryPath::parse('/examples/');

        $this->assertFileExists(
            $exampleFile->prepend($fileSystemService->getBasePath())->toAbsoluteFileSystemString()
        );
        $this->assertFileExists(
            $exampleDirectory->prepend($fileSystemService->getBasePath())->toAbsoluteFileSystemString()
        );

        $this->logIn($this->getUser(UserCommitter::class, $referenceRepository));
        $crawler = $this->client->request('GET', '/browse/examples/?action=remove');
//        $this->assertStatusCode(200, $client);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $submitButton = $crawler->selectButton('Remove all files');
        $form = $submitButton->form();
        $this->client->submit(
            $form,
            [
                'form[commitMessage]' => 'A test commit message'
            ]
        );
//        $this->assertStatusCode(302, $client);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('/browse/', $this->client->getResponse()->headers->get('Location'));

        $this->assertFileDoesNotExist(
            $exampleFile->prepend($fileSystemService->getBasePath())->toAbsoluteFileSystemString()
        );
        $this->assertFileDoesNotExist(
            $exampleDirectory->prepend($fileSystemService->getBasePath())->toAbsoluteFileSystemString()
        );
    }

    public function testRemoveNonEmptyDirectoryWithLockTest(): void
    {
        $referenceRepository = $this->loadClientAndFixtures([Users::class]);

        $exampleFile = FilePath::parse('/examples/toc-example.md');
        $exampleDirectory = DirectoryPath::parse('/examples/');

        /** @var FileSystemService $fileSystemService */
        $fileSystemService = self::getContainer()->get(FileSystemService::class);
        /** @var WikiService $wikiService */
        $wikiService = self::getContainer()->get(WikiService::class);
        $wikiService->createLock($this->getUser(UserAdmin::class, $referenceRepository), $exampleFile);

        $this->assertFileExists(
            $exampleFile->prepend($fileSystemService->getBasePath())->toAbsoluteFileSystemString()
        );
        $this->assertFileExists(
            $exampleDirectory->prepend($fileSystemService->getBasePath())->toAbsoluteFileSystemString()
        );

        $this->logIn($this->getUser(UserCommitter::class, $referenceRepository));
        $crawler = $this->client->request('GET', '/browse/examples/?action=remove');
//        $this->assertStatusCode(200, $client);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $submitButton = $crawler->selectButton('Remove all files');
        $form = $submitButton->form();
        $this->client->submit(
            $form,
            [
                'form[commitMessage]' => 'A test commit message'
            ]
        );
//        $this->assertStatusCode(500, $client);
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
    }
}
