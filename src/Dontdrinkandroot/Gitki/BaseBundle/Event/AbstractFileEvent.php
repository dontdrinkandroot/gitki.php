<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Event;

use Dontdrinkandroot\Gitki\BaseBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Component\EventDispatcher\Event;

class AbstractFileEvent extends Event
{

    /**
     * @var GitUserInterface
     */
    private $user;

    /**
     * @var string
     */
    private $commitMessage;

    /**
     * @var int
     */
    private $time;

    /**
     * @var FilePath
     */
    private $file;

    public function __construct(GitUserInterface $user, $commitMessage, $time, FilePath $file)
    {

        $this->user = $user;
        $this->commitMessage = $commitMessage;
        $this->time = $time;
        $this->file = $file;
    }

    /**
     * @return GitUserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getCommitMessage()
    {
        return $this->commitMessage;
    }

    /**
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return FilePath
     */
    public function getFile()
    {
        return $this->file;
    }
}
