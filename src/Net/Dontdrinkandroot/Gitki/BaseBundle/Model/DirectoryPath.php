<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Model;


class DirectoryPath extends Path
{

    public function __construct($pathString = null)
    {
        parent::__construct($pathString);
    }

    /**
     * @param $name
     * @return DirectoryPath
     */
    public function addSubDirectory($name)
    {
        $newPath = new DirectoryPath();
        $newSegments = array();
        $lastSegment = null;
        foreach ($this->segments as $segment) {
            $newSegments[] = $segment;
            $lastSegment = $segment;
        }

        $newSegments[] = new PathSegment($name, $lastSegment);
        $newPath->setSegments($newSegments);

        return $newPath;
    }

    /**
     * @param $name
     * @return FilePath
     */
    public function addFile($name)
    {
        $newPath = new FilePath();
        $newSegments = array();
        $lastSegment = null;
        foreach ($this->segments as $segment) {
            $newSegments[] = $segment;
            $lastSegment = $segment;
        }

        $newSegments[] = new PathSegment($name, $lastSegment);
        $newPath->setSegments($newSegments);

        return $newPath;
    }

    public function toString()
    {
        $lastSegment = $this->getLastSegment();
        if ($lastSegment === null) {
            return '';
        }

        return $lastSegment->toString() . '/';
    }

    public function __toString()
    {
        return $this->toString();
    }

} 