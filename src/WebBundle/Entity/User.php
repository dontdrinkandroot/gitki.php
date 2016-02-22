<?php

namespace Dontdrinkandroot\Gitki\WebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use FOS\UserBundle\Entity\User as BaseUser;

/**
 * User
 */
class User extends BaseUser implements GitUserInterface
{

    /**
     * @var int
     */
    private $googleId;

    /**
     * @var int
     */
    private $githubId;

    /**
     * @var int
     */
    private $facebookId;

    public function __construct()
    {
        BaseUser::__construct();
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

    /**
     * {@inheritdoc}
     */
    public function getGitUserName()
    {
        return $this->getUsername();
    }

    /**
     * {@inheritdoc}
     */
    public function getGitUserEmail()
    {
        return $this->getEmail();
    }

    /**
     * @return int
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    /**
     * @param int $facebookId
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;
    }

    public function __toString()
    {
        $s = 'id=' . $this->getId();
        $s .= ',realName=' . $this->getUsername();
        $s .= ',email=' . $this->getEmail();

        return $s;
    }
}
