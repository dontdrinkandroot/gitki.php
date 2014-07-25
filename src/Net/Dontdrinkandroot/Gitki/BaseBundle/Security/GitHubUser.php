<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Security;


class GitHubUser implements User
{

    protected $id;

    protected $login;

    protected $realName;

    protected $eMails;

    protected $roles = array('ROLE_USER', 'ROLE_GITHUB_USER');

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

    public function getName()
    {
        if (null !== $this->realName) {
            return $this->realName;
        }

        return $this->login;
    }

    public function getEmail()
    {
        $primaryEmail = null;
        foreach ($this->eMails as $eMail) {
            if ($eMail['primary']) {
                $primaryEmail = $eMail['email'];
            }
        }

        if (null === $primaryEmail) {
            throw new \Exception('No primary eMail address found');
        }

        return $primaryEmail;
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

    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

} 