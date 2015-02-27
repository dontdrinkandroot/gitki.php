<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Service;

use Dontdrinkandroot\Gitki\BaseBundle\Event\MarkdownDocumentDeletedEvent;
use Dontdrinkandroot\Gitki\BaseBundle\Event\MarkdownDocumentSavedEvent;
use Dontdrinkandroot\Gitki\BaseBundle\Repository\GitRepositoryInterface;
use Dontdrinkandroot\Gitki\MarkdownBundle\Service\MarkdownService;
use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Utils\StringUtils;
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
    public function savePage(UserInterface $user, FilePath $relativeFilePath, $content, $commitMessage)
    {
        $parsedMarkdownDocument = parent::savePage($user, $relativeFilePath, $content, $commitMessage);

        $this->eventDispatcher->dispatch(
            MarkdownDocumentSavedEvent::NAME,
            new MarkdownDocumentSavedEvent($relativeFilePath, $user, time(), $parsedMarkdownDocument, $commitMessage)
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

        if (StringUtils::endsWith($relativeOldFilePath->getName(), '.md')) {
            $this->eventDispatcher->dispatch(
                MarkdownDocumentDeletedEvent::NAME,
                new MarkdownDocumentDeletedEvent($relativeOldFilePath, $user, time(), $commitMessage)
            );
        }

        if (StringUtils::endsWith($relativeNewFilePath->getName(), '.md')) {
            $content = $this->getContent($relativeNewFilePath);
            $parsedMarkdownDocument = $this->markdownService->parse($relativeNewFilePath, $content);
            $this->eventDispatcher->dispatch(
                MarkdownDocumentSavedEvent::NAME,
                new MarkdownDocumentSavedEvent(
                    $relativeNewFilePath,
                    $user,
                    time(),
                    $parsedMarkdownDocument,
                    $commitMessage
                )
            );
        }
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
        if (StringUtils::endsWith($relativeFilePath->getName(), '.md')) {
            $this->eventDispatcher->dispatch(
                MarkdownDocumentDeletedEvent::NAME,
                new MarkdownDocumentDeletedEvent($relativeFilePath, $user, time(), $commitMessage)
            );
        }
    }
}