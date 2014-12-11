<?php

namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;

/**
 * User
 */
class User extends BaseUser
{

    /**
     * @var int
     */
    private $googleId;

    /**
     * @var int
     */
    private $githubId;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param int $githubId
     */
    public function setGithubId($githubId)
    {
        $this->githubId = $githubId;
    }

    /**
     * @return int
     */
    public function getGithubId()
    {
        return $this->githubId;
    }

    /**
     * @param int $googleId
     */
    public function setGoogleId($googleId)
    {
        $this->googleId = $googleId;
    }

    /**
     * @return int
     */
    public function getGoogleId()
    {
        return $this->googleId;
    }



    public function __toString()
    {
        $s = 'id=' . $this->getId();
        $s .= ',realName=' . $this->getUsername();
        $s .= ',email=' . $this->getEmail();

        return $s;
    }
}
