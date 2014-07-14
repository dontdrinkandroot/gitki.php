<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Model;


class FilePath extends Path
{

    public function __construct($pathString = null)
    {
        parent::__construct($pathString);
    }

    public function toString()
    {
        $lastSegment = $this->getLastSegment();
        if ($lastSegment === null) {
            return '';
        }

        return $lastSegment->toString();
    }

    public function __toString()
    {
        return $this->toString();
    }

} 