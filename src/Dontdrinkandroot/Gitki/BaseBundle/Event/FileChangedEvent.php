<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Event;

use Dontdrinkandroot\Gitki\BaseBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\FilePath;

class FileChangedEvent extends AbstractFileEvent
{

    const NAME = 'ddr.gitki.file.changed';

    /**
     * @var string
     */
    private $content;

    public function __construct(GitUserInterface $user, $commitMessage, $time, FilePath $file, $content)
    {
        parent::__construct($user, $commitMessage, $time, $file);
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
