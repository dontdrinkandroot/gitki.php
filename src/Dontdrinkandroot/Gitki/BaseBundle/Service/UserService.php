<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Service;

use Dontdrinkandroot\Gitki\BaseBundle\Entity\User;

interface UserService
{

    /**
     * @return User[]
     */
    public function listUsers();

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
