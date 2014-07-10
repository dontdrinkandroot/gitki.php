<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Security;


use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{

    protected $id;

    protected $login;

    protected $realName;

    protected $eMails;

    protected $roles = array('ROLE_OAUTH_USER');

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

    public function getUsername()
    {
        return $this->login;
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

    public function setLogin($login)
    {
        $this->login = $login;
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function getPrimaryEMail()
    {
        return $this->eMails[0];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getRealName()
    {
        return $this->realName;
    }

    public function setEMails($eMails)
    {
        $this->eMails = $eMails;
    }

    public function getEMails()
    {
        return $this->eMails;
    }

} 