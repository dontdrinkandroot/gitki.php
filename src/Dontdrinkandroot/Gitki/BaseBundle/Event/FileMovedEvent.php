<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Event;

use Dontdrinkandroot\Gitki\BaseBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\FilePath;

class FileMovedEvent extends AbstractFileEvent
{

    const NAME = 'ddr.gitki.file.moved';

    /**
     * @var FilePath
     */
    private $previousFile;

    public function __construct(GitUserInterface $user, $commitMessage, $time, FilePath $file, FilePath $previousFile)
    {
        parent::__construct($user, $commitMessage, $time, $file);
        $this->previousFile = $previousFile;
    }

    /**
     * @return FilePath
     */
    public function getPreviousFile()
    {
        return $this->previousFile;
    }
}
