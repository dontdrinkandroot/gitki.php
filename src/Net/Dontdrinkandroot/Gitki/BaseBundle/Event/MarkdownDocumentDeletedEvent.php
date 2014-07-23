<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Event;


use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\Path\FilePath;
use Symfony\Component\EventDispatcher\Event;

class MarkdownDocumentDeletedEvent extends Event
{

    /**
     * @var FilePath
     */
    private $path;
    private $login;
    private $time;
    private $commitMessage;

    public function __construct(FilePath $path, $login, $time, $commitMessage)
    {
        $this->path = $path;
        $this->login = $login;
        $this->time = $time;
        $this->commitMessage = $commitMessage;
    }

    public function setLogin($login)
    {
        $this->login = $login;
    }

    public function getLogin()
    {
        return $this->login;
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