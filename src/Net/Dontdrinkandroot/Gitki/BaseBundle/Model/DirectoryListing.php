<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Model;


class DirectoryListing
{

    private $path;

    private $pages;

    private $subdirectories;

    function __construct($path, $pages, $subdirectories)
    {
        $this->path = $path;
        $this->pages = $pages;
        $this->subdirectories = $subdirectories;
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

}