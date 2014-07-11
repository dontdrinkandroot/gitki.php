<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Model;


class PathSegment
{

    /**
     * @var PathSegment
     */
    private $parentSegment;

    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     * @param PathSegment $parentSegment
     */
    public function __construct($name, PathSegment $parentSegment = null)
    {
        $this->parentSegment = $parentSegment;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return PathSegment
     */
    public function getParentSegment()
    {
        return $this->parentSegment;
    }

    /**
     * @return string
     */
    public function toString()
    {
        if (null === $this->parentSegment) {
            return $this->name;
        }

        return $this->parentSegment->toString() . '/' . $this->name;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

} 