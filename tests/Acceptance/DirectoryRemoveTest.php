<?php

namespace App\Tests\Acceptance;

use App\DataFixtures\UserReferenceTrait;
use App\DataFixtures\Users;
use Dontdrinkandroot\GitkiBundle\Service\FileSystem\FileSystemService;
use Dontdrinkandroot\GitkiBundle\Service\Wiki\WikiService;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;

class DirectoryRemoveTest extends BaseAcceptanceTest
{
    use UserReferenceTrait;

    public function testRemoveEmptyDirectoryTest()
    {
        $referenceRepository = $this->loadFixtures([Users::class])->getReferenceRepository();
        $client = $this->makeBrowser();
        
        $directoryPath = DirectoryPath::parse('/testdirectory/');
        /** @var FileSystemService $fileSystemService */
        $fileSystemService = $this->getContainer()->get(
            'test.Dontdrinkandroot\GitkiBundle\Service\FileSystem\FileSystemService'
        );
        /** @var WikiService $wikiService */
        $wikiService = $this->getContainer()->get('test.Dontdrinkandroot\GitkiBundle\Service\Wiki\WikiService');
        $wikiService->createFolder($directoryPath);
        $this->assertFileExists(
            $directoryPath->prepend($fileSystemService->getBasePath())->toAbsoluteFileSystemString()
        );

        $this->login($client, $this->getUser(Users::COMMITTER, $referenceRepository));
        $client->request('GET', '/browse/testdirectory/?action=remove');
//        $this->assertStatusCode(302, $client);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals('/browse/', $client->getResponse()->headers->get('Location'));

        $this->assertFileNotExists(
            $directoryPath->prepend($fileSystemService->getBasePath())->toAbsoluteFileSystemString()
        );
    }

    public function testRemoveNonEmptyDirectoryTest()
    {
        $referenceRepository = $this->loadFixtures([Users::class])->getReferenceRepository();
        $client = $this->makeBrowser();
        
        /** @var FileSystemService $fileSystemService */
        $fileSystemService = $this->getContainer()->get(
            'test.Dontdrinkandroot\GitkiBundle\Service\FileSystem\FileSystemService'
        );

        $exampleFile = FilePath::parse('/examples/toc-example.md');
        $exampleDirectory = DirectoryPath::parse('/examples/');

        $this->assertFileExists(
            $exampleFile->prepend($fileSystemService->getBasePath())->toAbsoluteFileSystemString()
        );
        $this->assertFileExists(
            $exampleDirectory->prepend($fileSystemService->getBasePath())->toAbsoluteFileSystemString()
        );

        $this->logIn($client, $this->getUser(Users::COMMITTER, $referenceRepository));
        $crawler = $client->request('GET', '/browse/examples/?action=remove');
//        $this->assertStatusCode(200, $client);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $submitButton = $crawler->selectButton('Remove all files');
        $form = $submitButton->form();
        $client->submit(
            $form,
            [
                'form[commitMessage]' => 'A test commit message'
            ]
        );
//        $this->assertStatusCode(302, $client);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals('/browse/', $client->getResponse()->headers->get('Location'));

        $this->assertFileNotExists(
            $exampleFile->prepend($fileSystemService->getBasePath())->toAbsoluteFileSystemString()
        );
        $this->assertFileNotExists(
            $exampleDirectory->prepend($fileSystemService->getBasePath())->toAbsoluteFileSystemString()
        );
    }

    public function testRemoveNonEmptyDirectoryWithLockTest()
    {
        $referenceRepository = $this->loadFixtures([Users::class])->getReferenceRepository();
        $client = $this->makeBrowser();

        $exampleFile = FilePath::parse('/examples/toc-example.md');
        $exampleDirectory = DirectoryPath::parse('/examples/');

        /** @var FileSystemService $fileSystemService */
        $fileSystemService = $this->getContainer()->get(
            'test.Dontdrinkandroot\GitkiBundle\Service\FileSystem\FileSystemService'
        );
        /** @var WikiService $wikiService */
        $wikiService = $this->getContainer()->get('test.Dontdrinkandroot\GitkiBundle\Service\Wiki\WikiService');
        $wikiService->createLock($this->getUser(Users::ADMIN, $referenceRepository), $exampleFile);

        $this->assertFileExists(
            $exampleFile->prepend($fileSystemService->getBasePath())->toAbsoluteFileSystemString()
        );
        $this->assertFileExists(
            $exampleDirectory->prepend($fileSystemService->getBasePath())->toAbsoluteFileSystemString()
        );

        $this->logIn($client, $this->getUser(Users::COMMITTER, $referenceRepository));
        $crawler = $client->request('GET', '/browse/examples/?action=remove');
//        $this->assertStatusCode(200, $client);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $submitButton = $crawler->selectButton('Remove all files');
        $form = $submitButton->form();
        $client->submit(
            $form,
            [
                'form[commitMessage]' => 'A test commit message'
            ]
        );
//        $this->assertStatusCode(500, $client);
        $this->assertEquals(500, $client->getResponse()->getStatusCode());
    }
}
