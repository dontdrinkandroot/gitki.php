<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Event;

use Dontdrinkandroot\Gitki\BaseBundle\Entity\User;
use Dontdrinkandroot\Gitki\BaseBundle\Model\ParsedMarkdownDocument;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Security\Core\User\UserInterface;

class MarkdownDocumentSavedEvent extends Event
{

    const NAME = 'ddr.gitki.wiki.markdown_document.saved';

    /**
     * @var FilePath
     */
    private $path;

    private $time;

    private $document;

    private $commitMessage;

    /**
     * @var UserInterface
     */
    private $user;

    public function __construct(
        FilePath $path,
        UserInterface $user,
        $time,
        ParsedMarkdownDocument $document,
        $commitMessage
    )
    {
        $this->path = $path;
        $this->time = $time;
        $this->document = $document;
        $this->commitMessage = $commitMessage;
        $this->user = $user;
    }

    public function setDocument($content)
    {
        $this->document = $content;
    }

    public function getDocument()
    {
        return $this->document;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return FilePath
     */
    public function getPath()
    {
        return $this->path;
    }

    public function setTime($time)
    {
        $this->time = $time;
    }

    public function getTime()
    {
        return $this->time;
    }

    public function getCommitMessage()
    {
        return $this->commitMessage;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
