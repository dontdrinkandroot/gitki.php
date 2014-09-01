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

    /**
     * @return \Net\Dontdrinkandroot\Gitki\BaseBundle\Entity\User[]
     */
    public function listUsers();

    /**
     * @param User   $user
     * @param string $newPassword
     *
     * @return User
     */
    public function changePassword(\Net\Dontdrinkandroot\Gitki\BaseBundle\Entity\User $user, $newPassword);

    /**
     * @param \Net\Dontdrinkandroot\Gitki\BaseBundle\Entity\User $user
     *
     * @return mixed
     */
    public function saveUser(\Net\Dontdrinkandroot\Gitki\BaseBundle\Entity\User $user);

    /**
     * @param int $id
     *
     * @return \Net\Dontdrinkandroot\Gitki\BaseBundle\Entity\User|null
     */
    public function findUserById($id);
}