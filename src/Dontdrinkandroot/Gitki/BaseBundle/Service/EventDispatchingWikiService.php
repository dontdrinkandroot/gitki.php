<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Service;

use Dontdrinkandroot\Gitki\BaseBundle\Event\FileChangedEvent;
use Dontdrinkandroot\Gitki\BaseBundle\Event\FileDeletedEvent;
use Dontdrinkandroot\Gitki\BaseBundle\Event\FileMovedEvent;
use Dontdrinkandroot\Gitki\BaseBundle\Model\GitUserInterface;
use Dontdrinkandroot\Gitki\BaseBundle\Repository\GitRepositoryInterface;
use Dontdrinkandroot\Gitki\MarkdownBundle\Service\MarkdownService;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventDispatchingWikiService extends WikiService
{

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct(
        GitRepositoryInterface $gitRepository,
        LockService $lockService,
        MarkdownService $markdownService,
        EventDispatcherInterface $eventDispatcher
    ) {

        parent::__construct($gitRepository, $lockService, $markdownService);
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function saveFile(GitUserInterface $user, FilePath $relativeFilePath, $content, $commitMessage)
    {
        $parsedMarkdownDocument = parent::saveFile($user, $relativeFilePath, $content, $commitMessage);

        $this->eventDispatcher->dispatch(
            FileChangedEvent::NAME,
            new FileChangedEvent($user, $commitMessage, time(), $relativeFilePath, $content)
        );

        return $parsedMarkdownDocument;
    }

    /**
     * {@inheritdoc}
     */
    public function renameFile(
        GitUserInterface $user,
        FilePath $relativeOldFilePath,
        FilePath $relativeNewFilePath,
        $commitMessage
    ) {
        parent::renameFile($user, $relativeOldFilePath, $relativeNewFilePath, $commitMessage);

        $this->eventDispatcher->dispatch(
            FileMovedEvent::NAME,
            new FileMovedEvent($user, $commitMessage, time(), $relativeNewFilePath, $relativeOldFilePath)
        );
    }

    /**
     * @param GitUserInterface $user
     * @param FilePath      $relativeFilePath
     * @param string        $commitMessage
     *
     * @throws \Exception
     */
    public function deleteFile(GitUserInterface $user, FilePath $relativeFilePath, $commitMessage)
    {
        $this->eventDispatcher->dispatch(
            FileDeletedEvent::NAME,
            new FileDeletedEvent($user, $commitMessage, time(), $relativeFilePath)
        );
    }
}
