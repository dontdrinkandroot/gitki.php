<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserAdmin extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager): void
    {
        $user = new User(
            email: 'admin@example.com',
            realName: 'Admin User',
            roles: ['ROLE_ADMIN']
        );
        $user->password = $this->passwordHasher->hashPassword($user, 'admin');

        $manager->persist($user);
        $manager->flush();

        $this->addReference(self::class, $user);
    }
}
