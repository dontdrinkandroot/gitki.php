<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Repository;

use Dontdrinkandroot\Gitki\BaseBundle\Event\FileChangedEvent;
use Dontdrinkandroot\Gitki\BaseBundle\Event\FileDeletedEvent;
use Dontdrinkandroot\Gitki\BaseBundle\Event\FileMovedEvent;
use Dontdrinkandroot\Gitki\BaseBundle\Model\SearchResult;
use Dontdrinkandroot\Path\FilePath;

interface ElasticsearchRepositoryInterface
{

    /**
     * @param FileChangedEvent $event
     */
    public function onFileChanged(FileChangedEvent $event);

    /**
     * @param FileDeletedEvent $event
     */
    public function onFileDeleted(FileDeletedEvent $event);

    /**
     * @param FileMovedEvent $event
     */
    public function onFileMoved(FileMovedEvent $event);

    /**
     * @param $searchString
     *
     * @return SearchResult[]
     */
    public function search($searchString);

    /**
     * @param FilePath $path
     */
    public function addFile(FilePath $path);

    /**
     * @param FilePath $path
     */
    public function deleteFile(FilePath $path);

    /**
     * Clears the whole index.
     */
    public function clear();
}
