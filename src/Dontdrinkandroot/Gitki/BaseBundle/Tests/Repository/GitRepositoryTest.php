<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Tests\Repository;

use Dontdrinkandroot\Gitki\BaseBundle\Model\CommitMetadata;
use Dontdrinkandroot\Gitki\BaseBundle\Repository\GitRepository;
use Dontdrinkandroot\Gitki\BaseBundle\Tests\GitRepositoryTestCase;
use Net\Dontdrinkandroot\Utils\Path\FilePath;

class GitRepositoryTest extends GitRepositoryTestCase
{

    protected function createGitRepository()
    {
        return new GitRepository(self::TEST_PATH);
    }

    public function testAddAndCommit()
    {
        $gitRepository = $this->createGitRepository();
        $filePath = FilePath::parse('test.txt');
        $gitRepository->putContent($filePath, 'asdf');
        $gitRepository->addAndCommit('"Tester <test@example.com>"', 'Added test.txt', $filePath);
        $this->assertTrue($gitRepository->exists($filePath));

        $history = $gitRepository->getFileHistory($filePath);
        $this->assertCount(1, $history);

        /** @var CommitMetadata $firstEntry */
        $firstEntry = $history[0];
        $this->assertEquals('Added test.txt', $firstEntry->getMessage());
        $this->assertEquals('test@example.com', $firstEntry->getEMail());
        $this->assertEquals('Tester', $firstEntry->getCommitter());
    }
}
