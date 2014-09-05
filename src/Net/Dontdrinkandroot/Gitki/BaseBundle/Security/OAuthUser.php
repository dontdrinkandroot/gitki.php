<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Security;

class OAuthUser implements User
{

    /**
     * @var User
     */
    private $user;

    private $roles = ['ROLE_OAUTH_USER'];

    private $accessToken;

    private $realName;

    private $email;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function addRole($role)
    {
        $this->roles[] = $role;
    }

    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function setRealName($realName)
    {
        $this->realName = $realName;
    }

    public function setEmail($primaryEmailAddress)
    {
        $this->email = $primaryEmailAddress;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->user->getId();
    }

    /**
     * @inheritdoc
     */
    public function getRealName()
    {
        $knownRealName = $this->user->getRealName();
        if (!empty($knownRealName)) {
            return $knownRealName;
        }

        return $this->realName;
    }

    /**
     * @inheritdoc
     */
    public function getEmail()
    {
        $knownEmail = $this->user->getEmail();
        if (!empty($knownEmail)) {
            return $knownEmail;
        }

        return $this->email;
    }

    /**
     * @inheritdoc
     */
    public function getRoles()
    {
        $effectiveRoles = array_merge($this->roles, $this->user->getRoles());

        return $effectiveRoles;
    }

    /**
     * @inheritdoc
     */
    public function getPassword()
    {
        return $this->user->getPassword();
    }

    /**
     * @inheritdoc
     */
    public function getSalt()
    {
        return $this->user->getSalt();
    }

    /**
     * @inheritdoc
     */
    public function getUsername()
    {
        return $this->user->getUsername();
    }

    /**
     * @inheritdoc
     */
    public function eraseCredentials()
    {
        $this->user->eraseCredentials();
    }

    /**
     * @inheritdoc
     */
    public function getGithubLogin()
    {
        return $this->user->getGithubLogin();
    }

    /**
     * @inheritdoc
     */
    public function getGoogleLogin()
    {
        return $this->user->getGoogleLogin();
    }
}