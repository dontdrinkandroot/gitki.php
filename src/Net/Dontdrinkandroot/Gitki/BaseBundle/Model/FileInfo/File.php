<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Model\FileInfo;


use Net\Dontdrinkandroot\Utils\Path\FilePath;
use Net\Dontdrinkandroot\Utils\Path\Path;

class File extends PathAwareFileInfo
{

    /**
     * @var FilePath
     */
    protected $relativePath;

    /**
     * @var FilePath
     */
    protected $absolutePath;

    public function __construct($basePath, $currentDirectoryPath, $relativeFilePath)
    {
        parent::__construct($basePath . $currentDirectoryPath . $relativeFilePath);
        $this->absolutePath = FilePath::parse($currentDirectoryPath . $relativeFilePath);
        $this->relativePath = FilePath::parse($relativeFilePath);
    }

    /**
     * @return FilePath
     */
    public function getRelativePath()
    {
        return $this->relativePath;
    }

    /**
     * @return Path
     */
    public function getAbsolutePath()
    {
        return $this->absolutePath;
    }
}