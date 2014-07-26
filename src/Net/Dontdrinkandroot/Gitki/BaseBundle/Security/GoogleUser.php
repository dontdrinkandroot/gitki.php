<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Security;


class GoogleUser implements User
{

    protected $id;

    protected $realName;

    protected $eMail;

    protected $roles = array('ROLE_USER', 'ROLE_GOOGLE_USER');

    protected $accessToken;

    public function getRoles()
    {
        return $this->roles;
    }

    public function addRole($role)
    {
        $this->roles[] = $role;
    }

    public function getPassword()
    {
        return null;
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
        return true;
    }

    public function setRealName($realName)
    {
        $this->realName = $realName;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->realName;
    }

    public function getEmail()
    {
        return $this->eMail;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getRealName()
    {
        return $this->realName;
    }

    public function setEMail($eMail)
    {
        $this->eMail = $eMail;
    }

    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function getUsername()
    {
        return $this->eMail;
    }
} 