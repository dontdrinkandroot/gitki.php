<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserWatcher extends Fixture
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
            email: 'watcher@example.com',
            realName: 'Watcher User',
            roles: ['ROLE_WATCHER']
        );
        $user->password = $this->passwordHasher->hashPassword($user, 'watcher');

        $manager->persist($user);
        $manager->flush();

        $this->addReference(self::class, $user);
    }
}
