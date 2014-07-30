<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Model\Path;


use Net\Dontdrinkandroot\Symfony\ExtensionBundle\Utils\StringUtils;

class FilePath extends AbstractPath
{

    protected $fileName;

    protected $extension;

    public function __construct($name)
    {
        if (empty($name)) {
            throw new \Exception('Name must not be empty');
        }

        if (strpos($name, '/') !== false) {
            throw new \Exception('Name must not contain /');
        }

        $this->fileName = $name;
        $lastDotPos = strrpos($name, '.');
        if (false !== $lastDotPos && $lastDotPos > 0) {
            $this->fileName = substr($name, 0, $lastDotPos);
            $this->extension = substr($name, $lastDotPos + 1);
        }

        $this->parentPath = new DirectoryPath();
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        $name = $this->fileName;
        if (null !== $this->extension) {
            $name .= '.' . $this->extension;
        }

        return $name;
    }

    /**
     * @inheritdoc
     */
    public function toAbsoluteUrlString()
    {
        return $this->parentPath->toAbsoluteUrlString() . $this->getName();
    }

    /**
     * @inheritdoc
     */
    public function toRelativeUrlString()
    {
        return $this->parentPath->toRelativeUrlString() . $this->getName();
    }

    /**
     * @inheritdoc
     */
    public function toAbsoluteFileString()
    {
        return $this->parentPath->toAbsoluteFileString() . $this->getName();
    }

    /**
     * @inheritdoc
     */
    public function toRelativeFileString()
    {
        return $this->parentPath->toRelativeFileString() . $this->getName();
    }

    /**
     * @inheritdoc
     */
    public function prepend(DirectoryPath $path)
    {
        return FilePath::parse($path->toAbsoluteUrlString() . $this->toAbsoluteUrlString());
    }

    public function getExtension()
    {
        return $this->extension;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @inheritdoc
     */
    function isDirectoryPath()
    {
        return false;
    }

    /**
     * @param $pathString
     * @return FilePath
     * @throws \Exception
     */
    public static function parse($pathString)
    {
        if (empty($pathString)) {
            throw new \Exception('Path String must not be empty');
        }

        if (StringUtils::getLastChar($pathString) === '/') {
            throw new \Exception('Path String must not end with /');
        }

        $directoryPart = null;
        $filePart = $pathString;
        $lastSlashPos = strrpos($pathString, '/');
        if (false !== $lastSlashPos) {
            $directoryPart = substr($pathString, 0, $lastSlashPos + 1);
            $filePart = substr($pathString, $lastSlashPos + 1);
        }

        $filePath = new FilePath($filePart);

        if (null !== $directoryPart) {
            $filePath->setParentPath(DirectoryPath::parse($directoryPart));
        }

        return $filePath;
    }
} 