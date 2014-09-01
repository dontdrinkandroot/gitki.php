<?php

namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Entity\User;

class DoctrineUserRepository extends EntityRepository
{

    public function save(User $user)
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
}
