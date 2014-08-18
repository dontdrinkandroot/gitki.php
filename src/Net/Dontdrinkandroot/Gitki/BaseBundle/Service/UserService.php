<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Service;


use Net\Dontdrinkandroot\Gitki\BaseBundle\Security\User;

interface UserService
{

    /**
     * @param string $login
     *
     * @return User|null
     */
    public function findByGitHubLogin($login);

    /**
     * @param string $login
     *
     * @return User|null
     */
    public function findByGoogleLogin($login);
}