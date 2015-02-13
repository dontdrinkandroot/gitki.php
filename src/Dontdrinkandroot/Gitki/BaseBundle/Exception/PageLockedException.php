<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Exception;

class PageLockedException extends \Exception
{

    private $lockedBy;
    private $expires;

    public function __construct($lockedBy, $expires)
    {
        parent::__construct('Page is locked by ' . $lockedBy);
        $this->lockedBy = $lockedBy;
        $this->expires = $expires;
    }


    public function getExpires()
    {
        return $this->expires;
    }


    public function getLockedBy()
    {
        return $this->lockedBy;
    }
}
