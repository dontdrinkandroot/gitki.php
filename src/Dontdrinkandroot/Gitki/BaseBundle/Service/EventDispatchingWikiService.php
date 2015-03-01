<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Service;

use Dontdrinkandroot\Gitki\BaseBundle\Event\FileChangedEvent;
use Dontdrinkandroot\Gitki\BaseBundle\Event\FileDeletedEvent;
use Dontdrinkandroot\Gitki\BaseBundle\Event\FileMovedEvent;
use Dontdrinkandroot\Gitki\BaseBundle\Event\MarkdownDocumentDeletedEvent;
use Dontdrinkandroot\Gitki\BaseBundle\Event\MarkdownDocumentSavedEvent;
use Dontdrinkandroot\Gitki\BaseBundle\Repository\GitRepositoryInterface;
use Dontdrinkandroot\Gitki\MarkdownBundle\Service\MarkdownService;
use Dontdrinkandroot\Path\FilePath;
use FOS\UserBundle\Model\UserInterface;
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
    public function saveFile(UserInterface $user, FilePath $relativeFilePath, $content, $commitMessage)
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
        UserInterface $user,
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
     * @param UserInterface $user
     * @param FilePath      $relativeFilePath
     * @param string        $commitMessage
     *
     * @throws \Exception
     */
    public function deleteFile(UserInterface $user, FilePath $relativeFilePath, $commitMessage)
    {
        $this->eventDispatcher->dispatch(
            FileDeletedEvent::NAME,
            new FileDeletedEvent($user, $commitMessage, time(), $relativeFilePath)
        );
    }
}
