<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Security;


use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{

    protected $id;

    protected $login;

    protected $realName;

    protected $eMail;

    public function getRoles()
    {
        return array('ROLE_USER', 'ROLE_OAUTH_USER');
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

    public function setEMail($eMail)
    {
        $this->eMail = $eMail;
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

    public function getEMail()
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

} 