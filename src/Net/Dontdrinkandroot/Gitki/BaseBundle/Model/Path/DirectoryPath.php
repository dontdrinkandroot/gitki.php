<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Model\Path;


use Net\Dontdrinkandroot\Symfony\ExtensionBundle\Utils\StringUtils;

class DirectoryPath extends AbstractPath
{

    protected $name;

    public function __construct($name = null)
    {
        if (strpos($name, '/') !== false) {
            throw new \Exception('Name must not contain /');
        }

        if (!empty($name)) {
            $this->name = $name;
            $this->parentPath = new DirectoryPath();
        }
    }

    /**
     * @param $name
     * @return DirectoryPath
     */
    public function appendDirectory($name)
    {
        if (empty($name)) {
            throw new \Exception('Name must not be empty');
        }

        if (strpos($name, '/') !== false) {
            throw new \Exception('Name must not contain /');
        }

        $directoryPath = new DirectoryPath($name);
        $directoryPath->setParentPath($this);

        return $directoryPath;
    }

    /**
     * @param $name
     * @return FilePath
     */
    public function appendFile($name)
    {
        if (empty($name)) {
            throw new \Exception('Name must not be empty');
        }

        if (strpos($name, '/') !== false) {
            throw new \Exception('Name must not contain /');
        }

        $filePath = new FilePath($name);
        $filePath->setParentPath($this);

        return $filePath;
    }

    /**
     * @inheritdoc
     */
    public function toAbsoluteUrlString()
    {
        if (null === $this->parentPath) {
            return '/';
        }

        return $this->parentPath->toAbsoluteUrlString() . $this->name . '/';
    }

    /**
     * @inheritdoc
     */
    public function toRelativeUrlString()
    {
        if (null === $this->parentPath) {
            return '';
        }

        return $this->parentPath->toRelativeUrlString() . $this->name . '/';
    }

    /**
     * @inheritdoc
     */
    public function toAbsoluteFileString()
    {
        if (null === $this->parentPath) {
            return DIRECTORY_SEPARATOR;
        }

        return $this->parentPath->toAbsoluteUrlString() . $this->name . DIRECTORY_SEPARATOR;
    }

    /**
     * @inheritdoc
     */
    public function toRelativeFileString()
    {
        if (null === $this->parentPath) {
            return '';
        }

        return $this->parentPath->toRelativeFileString() . $this->name . DIRECTORY_SEPARATOR;
    }

    /**
     * @inheritdoc
     */
    public function prepend(DirectoryPath $path)
    {
        return DirectoryPath::parse($path->toAbsoluteUrlString() . $this->toAbsoluteUrlString());
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    public function isRoot()
    {
        return null === $this->parentPath && null === $this->name;
    }

    /**
     * @param $pathString
     * @return DirectoryPath
     * @throws \Exception
     */
    public static function parse($pathString)
    {
        if (empty($pathString)) {
            return new DirectoryPath();
        }

        if (!(StringUtils::getLastChar($pathString) === '/')) {
            throw new \Exception('Path String must end with /');
        }

        return self::parseDirectoryPath($pathString, new DirectoryPath());
    }

    public function appendPathString($pathString)
    {
        $lastPath = $this;

        $filePart = null;
        $directoryPart = $pathString;
        if (!StringUtils::endsWith($pathString, '/')) {
            $filePart = $pathString;
            $lastSlashPos = strrpos($pathString, '/');
            if (false !== $lastSlashPos) {
                $directoryPart = substr($pathString, 0, $lastSlashPos + 1);
                $filePart = substr($pathString, $lastSlashPos + 1);
            }
        }

        $directoryPath = self::parseDirectoryPath($directoryPart, $lastPath);

        if (null !== $filePart) {
            $filePath = new FilePath($filePart);
            $filePath->setParentPath($directoryPath);

            return $filePath;
        }

        return $directoryPath;
    }


    protected static function parseDirectoryPath($pathString, DirectoryPath $rootPath)
    {
        $lastPath = $rootPath;
        if (null !== $pathString) {
            $parts = explode('/', $pathString);
            foreach ($parts as $part) {
                $trimmedPart = trim($part);
                if ($trimmedPart === '..') {
                    if (!$lastPath->hasParentPath()) {
                        throw new \Exception('Exceeding root level');
                    }
                    $lastPath = $lastPath->getParentPath();
                } else {
                    if ($trimmedPart !== "" && $trimmedPart !== '.') {
                        $directoryPath = new DirectoryPath($trimmedPart);
                        if (null !== $lastPath) {
                            $directoryPath->setParentPath($lastPath);
                        }
                        $lastPath = $directoryPath;
                    }
                }
            }
        }

        return $lastPath;
    }

} 