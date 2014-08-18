<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Security;


class OAuthUser implements User
{

    private $user;

    private $roles = ['ROLE_OAUTH_USER'];

    private $accessToken;

    private $realName;

    private $emailAddresses;

    private $primaryEmailAddress;

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

    public function setEmailAddresses($emailAddresses)
    {
        $this->emailAddresses = $emailAddresses;
    }

    public function setPrimaryEmailAddress($primaryEmailAddress)
    {
        $this->primaryEmailAddress = $primaryEmailAddress;
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
    public function getPrimaryEmailAddress()
    {
        $knownPrimaryEmailAddress = $this->user->getPrimaryEmailAddress();
        if (!empty($knownPrimaryEmailAddress)) {
            return $knownPrimaryEmailAddress;
        }

        return $this->primaryEmailAddress;
    }

    /**
     * @inheritdoc
     */
    public function getRoles()
    {
        return array_merge($this->roles, $this->user->getRoles());
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
}