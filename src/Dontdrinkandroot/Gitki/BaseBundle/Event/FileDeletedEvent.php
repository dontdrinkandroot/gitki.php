<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Event;

use Dontdrinkandroot\Gitki\BaseBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\FilePath;

class FileDeletedEvent extends AbstractFileEvent
{

    const NAME = 'ddr.gitki.file.deleted';

    public function __construct(GitUserInterface $user, $commitMessage, $time, FilePath $file)
    {
        parent::__construct($user, $commitMessage, $time, $file);
    }
}
