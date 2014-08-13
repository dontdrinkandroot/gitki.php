<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Event;


use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\ParsedMarkdownDocument;
use Net\Dontdrinkandroot\Utils\Path\FilePath;
use Symfony\Component\EventDispatcher\Event;

class MarkdownDocumentSavedEvent extends Event
{

    /**
     * @var FilePath
     */
    private $path;
    private $email;
    private $time;
    private $document;
    private $commitMessage;

    public function __construct(FilePath $path, $email, $time, ParsedMarkdownDocument $document, $commitMessage)
    {
        $this->path = $path;
        $this->email = $email;
        $this->time = $time;
        $this->document = $document;
        $this->commitMessage = $commitMessage;
    }

    public function setDocument($content)
    {
        $this->document = $content;
    }

    public function getDocument()
    {
        return $this->document;
    }

    public function setEmail($login)
    {
        $this->email = $login;
    }

    public function getEmail()
    {
        return $this->email;
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
} 