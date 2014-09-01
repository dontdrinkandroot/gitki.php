<?php

namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Entity\User;

class DoctrineUserRepository extends EntityRepository
{

    public function save(User $user)
    {
        var_dump($this->getEntityManager());
        $this->getEntityManager()->persist($user);
    }
}
