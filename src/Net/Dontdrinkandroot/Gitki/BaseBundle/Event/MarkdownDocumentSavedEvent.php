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
    private $login;
    private $time;
    private $content;
    private $commitMessage;

    public function __construct(FilePath $path, $login, $time, $content, $commitMessage)
    {
        $this->path = $path;
        $this->login = $login;
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