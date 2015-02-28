<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Service;

use Dontdrinkandroot\Gitki\BaseBundle\Model\FileInfo\PageFile;
use Dontdrinkandroot\Gitki\BaseBundle\Repository\ElasticsearchRepository;
use Dontdrinkandroot\Gitki\BaseBundle\Repository\GitRepository;
use Dontdrinkandroot\Gitki\MarkdownBundle\Service\MarkdownService;
use Dontdrinkandroot\Path\DirectoryPath;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\SplFileInfo;

class ElasticsearchWikiService extends WikiService
{

    /**
     * @var \Dontdrinkandroot\Gitki\BaseBundle\Repository\ElasticsearchRepository
     */
    protected $elasticsearchRepository;

    public function __construct(
        GitRepository $gitRepository,
        MarkdownService $markdownService,
        EventDispatcherInterface $eventDispatcher,
        ElasticsearchRepository $elasticsearchRepository
    ) {
        parent::__construct($gitRepository, $markdownService, $eventDispatcher);
        $this->elasticsearchRepository = $elasticsearchRepository;
    }

    protected function createPageFile(DirectoryPath $repositoryPath, DirectoryPath $directoryPath, SplFileInfo $file)
    {
        $pageFile = new PageFile(
            $repositoryPath->toAbsoluteFileString(),
            $directoryPath->toRelativeFileString(),
            $file->getRelativePathName()
        );

        $title = $this->elasticsearchRepository->getTitle($pageFile->getAbsolutePath());
        if (empty($title)) {
            $title = $pageFile->getRelativePath()->getFileName();
        }

        $pageFile->setTitle($title);

        return $pageFile;
    }
}
