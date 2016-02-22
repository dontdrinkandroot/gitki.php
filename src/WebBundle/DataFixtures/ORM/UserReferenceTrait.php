<?php


namespace Dontdrinkandroot\Gitki\WebBundle\DataFixtures\ORM;

use Dontdrinkandroot\Gitki\WebBundle\Entity\User;

trait UserReferenceTrait
{

    /**
     * @param string $name
     *
     * @return User
     */
    public function getUser($name)
    {
        return $this->getReference($name);
    }
}