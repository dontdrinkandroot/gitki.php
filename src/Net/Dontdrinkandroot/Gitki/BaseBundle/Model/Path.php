<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Model;


class Path
{

    /**
     * @var PathSegment[]
     */
    private $segments = array();

    public function __construct($pathString = null)
    {
        if (null !== $pathString) {
            $parts = explode('/', $pathString);
            $parentSegment = null;
            foreach ($parts as $part) {
                if (trim($part) !== "") {
                    $parentSegment = new PathSegment($part, $parentSegment);
                    $this->segments[] = $parentSegment;
                }
            }
        }
    }

    /**
     * @return PathSegment[]
     */
    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * @param int $index
     * @return PathSegment
     */
    public function getSegment($index)
    {
        return $this->segments[$index];
    }

    /**
     * @return int
     */
    public function getNumSegments()
    {
        return count($this->segments);
    }

    /**
     * @return PathSegment|null
     */
    public function getLastSegment()
    {
        $numSegments = $this->getNumSegments();
        if ($numSegments == 0) {
            return null;
        }

        return $this->getSegment($numSegments - 1);
    }

    /**
     * @return null|string
     */
    public function getLastSegmentName()
    {
        $lastSegment = $this->getLastSegment();
        if ($lastSegment === null) {
            return null;
        }

        return $lastSegment->getName();
    }

    /**
     * @return bool
     */
    public function hasParentPath()
    {
        return $this->getNumSegments() > 0;
    }

    /**
     * @return Path|null
     */
    public function getParentPath()
    {
        if (0 == $this->getNumSegments()) {
            return null;
        }

        $parentPath = new Path();
        $parentSegments = array();
        for ($i = 0; $i < count($this->segments) - 1; $i++) {
            $parentSegments[$i] = $this->segments[$i];
        }
        $parentPath->setSegments($parentSegments);

        return $parentPath;
    }

    /**
     * @param $name
     * @return Path
     */
    public function addSegment($name)
    {
        $newPath = new Path();
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
     * @param PathSegment[] $segments
     */
    protected function setSegments($segments)
    {
        $this->segments = $segments;
    }

}