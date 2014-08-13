<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Event;


use Net\Dontdrinkandroot\Utils\Path\FilePath;
use Symfony\Component\EventDispatcher\Event;

class MarkdownDocumentDeletedEvent extends Event
{

    /**
     * @var FilePath
     */
    private $path;
    private $email;
    private $time;
    private $commitMessage;

    public function __construct(FilePath $path, $email, $time, $commitMessage)
    {
        $this->path = $path;
        $this->email = $email;
        $this->time = $time;
        $this->commitMessage = $commitMessage;
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