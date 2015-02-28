<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Event;

use Dontdrinkandroot\Path\FilePath;
use FOS\UserBundle\Model\UserInterface;

class FileMovedEvent extends AbstractFileEvent
{

    const NAME = 'ddr.gitki.file.moved';

    /**
     * @var FilePath
     */
    private $previousFile;

    public function __construct(UserInterface $user, $commitMessage, $time, FilePath $file, FilePath $previousFile)
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
