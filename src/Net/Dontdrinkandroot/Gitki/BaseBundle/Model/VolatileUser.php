<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Model;


use Net\Dontdrinkandroot\Gitki\BaseBundle\Security\User;

class VolatileUser implements User
{

    private $id;

    private $realName;

    private $primaryEmailAddress;

    private $emailAddresses;

    private $roles = ['ROLE_USER'];

    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getRealName()
    {
        return $this->realName;
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryEmailAddress()
    {
        return $this->primaryEmailAddress;
    }

    /**
     * @inheritdoc
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @inheritdoc
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getUsername()
    {
        return $this->getId();
    }

    /**
     * @inheritdoc
     */
    public function eraseCredentials()
    {
        /* Noop */
    }

    /**
     * @param string[] $emailAddresses
     */
    public function setEmailAddresses($emailAddresses)
    {
        $this->emailAddresses = $emailAddresses;
    }

    /**
     * @return string[]
     */
    public function getEmailAddresses()
    {
        return $this->emailAddresses;
    }

    public function addRole($role)
    {
        $this->roles[] = $role;
    }

    public function setRealName($realName)
    {
        $this->realName = $realName;
    }

    public function setPrimaryEmailAddress($primaryEmailAddress)
    {
        $this->primaryEmailAddress = $primaryEmailAddress;
    }

}