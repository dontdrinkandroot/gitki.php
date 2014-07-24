<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Model\Path;


use Net\Dontdrinkandroot\Symfony\ExtensionBundle\Utils\StringUtils;

class DirectoryPath extends AbstractPath
{

    protected $name;

    public function __construct($name = null)
    {
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
    public function toUrlString()
    {
        if (null === $this->parentPath) {
            return '/';
        }

        return $this->parentPath->toUrlString() . $this->name . '/';
    }

    /**
     * @inheritdoc
     */
    public function toFileString()
    {
        if (null === $this->parentPath) {
            return DIRECTORY_SEPARATOR;
        }

        return $this->parentPath->toUrlString() . $this->name . DIRECTORY_SEPARATOR;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $pathString
     * @return DirectoryPath
     * @throws \Exception
     */
    public static function parse($pathString)
    {
        if (StringUtils::getFirstChar($pathString) !== '/') {
            throw new \Exception('Path String must start with /');
        }

        if (!(StringUtils::getLastChar($pathString) === '/')) {
            throw new \Exception('Path String must end with /');
        }

        /* Root */
        $lastPath = new DirectoryPath();

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