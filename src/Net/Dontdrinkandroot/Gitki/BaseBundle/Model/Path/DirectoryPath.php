<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Model\Path;


use Net\Dontdrinkandroot\Symfony\ExtensionBundle\Utils\StringUtils;

class DirectoryPath implements Path
{

    protected $name;

    /**
     * @var DirectoryPath
     */
    protected $parentPath;

    public function __construct($name = null)
    {
        if (!empty($name)) {
            $this->name = $name;
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

        if (empty($this->name)) {
            return new DirectoryPath($name);
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

        if (empty($this->name)) {
            return new FilePath($name);
        }

        $filePath = new FilePath($name);
        $filePath->setParentPath($this);

        return $filePath;
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        if (null === $this->parentPath) {
            if (empty($this->name)) {
                return '';
            }

            return $this->name . '/';
        }

        return $this->parentPath->toString() . $this->name . '/';
    }


    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function hasParentPath()
    {
        if (null === $this->parentPath) {
            if (!empty($this->name)) {
                return true;
            }

            return false;
        }

        return true;
    }

    /**
     * @return DirectoryPath|null
     */
    public function getParentPath()
    {
        if (null !== $this->parentPath) {
            return $this->parentPath;
        }

        if (!empty($this->name)) {
            return new DirectoryPath();
        }

        return null;
    }

    public function setParentPath(DirectoryPath $path)
    {
        $this->parentPath = $path;
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

    public function __toString()
    {
        return $this->toString();
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

        if (StringUtils::getFirstChar($pathString) === '/') {
            throw new \Exception('Path String must be relative');
        }

        if (!(StringUtils::getLastChar($pathString) === '/')) {
            throw new \Exception('Path String must end with /');
        }

        $lastPath = null;
        if (null !== $pathString) {
            $parts = explode('/', $pathString);
            foreach ($parts as $part) {
                $trimmedPart = trim($part);
                if ($trimmedPart === '..') {
                    throw new \Exception('.. not supported');
                }
                if ($trimmedPart !== "" && $trimmedPart !== '.') {
                    $directoryPath = new DirectoryPath($trimmedPart);
                    if (null !== $lastPath) {
                        $directoryPath->setParentPath($lastPath);
                    }
                    $lastPath = $directoryPath;
                }
            }
        }

        return $lastPath;
    }

} 