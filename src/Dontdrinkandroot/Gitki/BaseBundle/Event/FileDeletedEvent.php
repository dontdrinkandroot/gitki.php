<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Event;

use Dontdrinkandroot\Path\FilePath;
use FOS\UserBundle\Model\UserInterface;

class FileDeletedEvent extends AbstractFileEvent
{

    const NAME = 'ddr.gitki.file.deleted';

    public function __construct(UserInterface $user, $commitMessage, $time, FilePath $file)
    {
        parent::__construct($user, $commitMessage, $time, $file);
    }
}
