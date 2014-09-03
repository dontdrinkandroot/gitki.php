<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Service;

use Net\Dontdrinkandroot\Gitki\BaseBundle\Entity\User;

interface UserService
{

    /**
     * @param string $email
     *
     * @return User|null
     */
    public function findByEmail($email);

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
     * @return User[]
     */
    public function listUsers();

    /**
     * @param User   $user
     * @param string $newPassword
     *
     * @return User
     */
    public function changePassword(User $user, $newPassword);

    /**
     * @param User $user
     *
     * @return mixed
     */
    public function saveUser(User $user);

    /**
     * @param int $id
     *
     * @return User|null
     */
    public function findUserById($id);

    /**
     * @param User $user
     *
     * @return mixed
     */
    public function deleteUser(User $user);
}