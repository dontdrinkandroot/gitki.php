<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Model;


class Path
{

    /**
     * @var PathSegment[]
     */
    private $segments = array();

    public function __construct($pathString)
    {
        $parts = explode('/', $pathString);
        $parentSegment = null;
        foreach ($parts as $part) {
            if (trim($part) !== "") {
                $parentSegment = new PathSegment($part, $parentSegment);
                $this->segments[] = $parentSegment;
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

}