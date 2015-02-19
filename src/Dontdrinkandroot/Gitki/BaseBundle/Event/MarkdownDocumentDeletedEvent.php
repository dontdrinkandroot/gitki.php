<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Event;

use Dontdrinkandroot\Gitki\BaseBundle\Entity\User;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Component\EventDispatcher\Event;

class MarkdownDocumentDeletedEvent extends Event
{

    /**
     * @var FilePath
     */
    private $path;
    private $time;
    private $commitMessage;

    /**
     * @var User
     */
    private $user;

    public function __construct(FilePath $path, User $user, $time, $commitMessage)
    {
        $this->path = $path;
        $this->time = $time;
        $this->commitMessage = $commitMessage;
        $this->user = $user;
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
