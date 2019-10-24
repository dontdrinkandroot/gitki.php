<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Model\UserManagerInterface;

class Users extends Fixture
{
    const WATCHER = 'user_watcher';
    const COMMITTER = 'user_committer';
    const ADMIN = 'user_admin';

    /** @var UserManagerInterface */
    private $userManager;

    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var User $user */
        $user = $this->userManager->createUser();
        $user->setUsername('watcher');
        $user->setRealName('Watcher User');
        $user->setEmail('watcher@example.com');
        $user->setPlainPassword('watcher');
        $user->addRole('ROLE_WATCHER');
        $user->setEnabled(true);

        $this->userManager->updateUser($user);
        $this->addReference(self::WATCHER, $user);

        $user = $this->userManager->createUser();
        $user->setUsername('committer');
        $user->setRealName('Committer User');
        $user->setEmail('committer@example.com');
        $user->setPlainPassword('committer');
        $user->addRole('ROLE_WATCHER');
        $user->addRole('ROLE_COMMITTER');
        $user->setEnabled(true);

        $this->userManager->updateUser($user);
        $this->addReference(self::COMMITTER, $user);

        $user = $this->userManager->createUser();
        $user->setUsername('admin');
        $user->setRealName('Admin User');
        $user->setEmail('admin@example.com');
        $user->setPlainPassword('admin');
        $user->addRole('ROLE_WATCHER');
        $user->addRole('ROLE_COMMITTER');
        $user->addRole('ROLE_ADMIN');
        $user->setEnabled(true);

        $this->userManager->updateUser($user);
        $this->addReference(self::ADMIN, $user);
    }
}
