<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Repository;

use Dontdrinkandroot\Gitki\BaseBundle\Event\FileChangedEvent;
use Dontdrinkandroot\Gitki\BaseBundle\Event\FileDeletedEvent;
use Dontdrinkandroot\Gitki\BaseBundle\Event\FileMovedEvent;
use Dontdrinkandroot\Path\FilePath;

class NoopElasticsearchRepository implements ElasticsearchRepositoryInterface
{

    /**
     * {@inheritdoc}
     */
    public function onFileChanged(FileChangedEvent $event)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    public function onFileDeleted(FileDeletedEvent $event)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    public function onFileMoved(FileMovedEvent $event)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    public function search($searchString)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function addFile(FilePath $path)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    public function deleteFile(FilePath $path)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        /* NOOP */
    }
}
