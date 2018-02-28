<?php

namespace App\DataFixtures;

use App\Entity\User;

trait UserReferenceTrait
{
    use ReferenceTrait;

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
