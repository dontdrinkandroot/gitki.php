<?php

namespace Dontdrinkandroot\Gitki\WebBundle\Entity;

use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * User
 */
class User extends BaseUser implements GitUserInterface
{
    /**
     * @var string
     */
    private $realName;

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
        return $this->getRealName();
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

    /**
     * @return string
     */
    public function getRealName()
    {
        return $this->realName;
    }

    /**
     * @param string $realName
     */
    public function setRealName($realName)
    {
        $this->realName = $realName;
    }

    public function __toString()
    {
        $s = 'id=' . $this->getId();
        $s .= ',realName=' . $this->getRealName();
        $s .= ',email=' . $this->getEmail();

        return $s;
    }
}
