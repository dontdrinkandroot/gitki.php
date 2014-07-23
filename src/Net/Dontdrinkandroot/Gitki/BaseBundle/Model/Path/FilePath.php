<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Model\Path;


use Net\Dontdrinkandroot\Symfony\ExtensionBundle\Utils\StringUtils;

class FilePath implements Path
{

    /**
     * @var DirectoryPath
     */
    protected $parentPath;

    protected $fileName;

    protected $extension;

    public function __construct($name)
    {
        $this->fileName = $name;
        $lastDotPos = strrpos($name, '.');
        if (false !== $lastDotPos && $lastDotPos > 0) {
            $this->fileName = substr($name, 0, $lastDotPos);
            $this->extension = substr($name, $lastDotPos + 1);
        }
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
    public function toString()
    {
        $str = '';
        if (null !== $this->parentPath) {
            $str .= $this->parentPath->toString();
        }

        $str .= $this->getName();

        return $str;
    }

    /**
     * @inheritdoc
     */
    public function hasParentPath()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getParentPath()
    {
        if (null !== $this->parentPath) {
            return $this->parentPath;
        }

        return new DirectoryPath();
    }

    public function setParentPath(DirectoryPath $path)
    {
        $this->parentPath = $path;
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function getExtension()
    {
        return $this->extension;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function collectParentPaths()
    {
        if (!$this->hasParentPath()) {
            return array();
        }

        return $this->getParentPath()->collectPaths();
    }

    public function collectPaths()
    {
        if (!$this->hasParentPath()) {
            return array($this);
        }

        return array_merge($this->getParentPath()->collectPaths(), array($this));
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

        if (StringUtils::getFirstChar($pathString) === '/') {
            throw new \Exception('Path String must be relative');
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