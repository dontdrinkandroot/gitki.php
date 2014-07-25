<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Event;


use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\Path\FilePath;
use Symfony\Component\EventDispatcher\Event;

class MarkdownDocumentSavedEvent extends Event
{

    /**
     * @var \Net\Dontdrinkandroot\Gitki\BaseBundle\Model\Path\FilePath
     */
    private $path;
    private $email;
    private $time;
    private $content;
    private $commitMessage;

    public function __construct(FilePath $path, $email, $time, $content, $commitMessage)
    {
        $this->path = $path;
        $this->email = $email;
        $this->time = $time;
        $this->content = $content;
        $this->commitMessage = $commitMessage;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
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