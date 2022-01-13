<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCommitter extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $user = new User(
            email: 'committer@example.com',
            realName: 'Committer User',
            roles: ['ROLE_WATCHER', 'ROLE_COMMITTER']
        );
        $user->password = $this->passwordHasher->hashPassword($user, 'committer');

        $manager->persist($user);
        $manager->flush();

        $this->addReference(Users::COMMITTER, $user);
        $this->addReference(self::class, $user);
    }
}
