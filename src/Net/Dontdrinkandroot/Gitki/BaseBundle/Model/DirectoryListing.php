<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Model;


class DirectoryListing
{

    private $path;

    private $pages;

    private $subdirectories;

    private $otherFiles;

    function __construct($path, $pages, $subdirectories, $otherFiles)
    {
        $this->path = $path;
        $this->pages = $pages;
        $this->subdirectories = $subdirectories;
        $this->otherFiles = $otherFiles;
    }

    public function getPages()
    {
        return $this->pages;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getSubdirectories()
    {
        return $this->subdirectories;
    }

    public function getOtherFiles()
    {
        return $this->otherFiles;
    }

}