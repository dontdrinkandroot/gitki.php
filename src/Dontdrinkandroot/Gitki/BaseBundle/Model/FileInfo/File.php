<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Model\FileInfo;

use Dontdrinkandroot\Path\FilePath;

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
     * @return FilePath
     */
    public function getAbsolutePath()
    {
        return $this->absolutePath;
    }
}
