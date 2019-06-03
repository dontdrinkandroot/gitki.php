<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Common\DataFixtures\ReferenceRepository;

trait UserReferenceTrait
{
    public function getUser(string $name, ReferenceRepository $referenceRepository): User
    {
        return $referenceRepository->getReference($name);
    }
}
