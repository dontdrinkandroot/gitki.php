<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Model;

use Dontdrinkandroot\Gitki\BaseBundle\Model\FileInfo\Directory;
use Dontdrinkandroot\Gitki\BaseBundle\Model\FileInfo\File;
use Dontdrinkandroot\Gitki\BaseBundle\Model\FileInfo\PageFile;
use Dontdrinkandroot\Path\DirectoryPath;

class DirectoryListing
{

    /**
     * @var DirectoryPath
     */
    private $path;

    /**
     * @var PageFile[]
     */
    private $pages;

    /**
     * @var Directory[]
     */
    private $subdirectories;

    /**
     * @var File[]
     */
    private $otherFiles;

    /**
     * @param DirectoryPath $path
     * @param PageFile[] $pages
     * @param Directory[] $subdirectories
     * @param File[] $otherFiles
     */
    public function __construct($path, $pages, $subdirectories, $otherFiles)
    {
        $this->path = $path;
        $this->pages = $pages;
        $this->subdirectories = $subdirectories;
        $this->otherFiles = $otherFiles;
    }

    /**
     * @return PageFile[]
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @return DirectoryPath
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return Directory[]
     */
    public function getSubdirectories()
    {
        return $this->subdirectories;
    }

    /**
     * @return File[]
     */
    public function getOtherFiles()
    {
        return $this->otherFiles;
    }
}
