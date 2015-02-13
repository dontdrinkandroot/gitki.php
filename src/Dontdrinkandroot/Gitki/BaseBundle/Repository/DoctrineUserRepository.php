<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Dontdrinkandroot\Gitki\BaseBundle\Entity\User;

class DoctrineUserRepository extends EntityRepository
{

    public function save(User $user)
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function remove($user)
    {
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
    }
}
