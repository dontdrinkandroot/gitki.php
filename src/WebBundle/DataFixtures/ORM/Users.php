<?php

namespace Dontdrinkandroot\Gitki\WebBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Dontdrinkandroot\Gitki\WebBundle\Entity\User;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Users extends AbstractFixture implements ContainerAwareInterface
{
    const WATCHER = 'user_watcher';
    const COMMITTER = 'user_committer';
    const ADMIN = 'user_admin';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var UserManagerInterface $userManager */
        $userManager = $this->container->get('fos_user.user_manager');

        /** @var User $user */
        $user = $userManager->createUser();
        $user->setUsername('watcher');
        $user->setRealName('Watcher User');
        $user->setEmail('watcher@example.com');
        $user->setPlainPassword('watcher');
        $user->addRole('ROLE_WATCHER');
        $user->setEnabled(true);

        $userManager->updateUser($user);
        $this->addReference(self::WATCHER, $user);

        $user = $userManager->createUser();
        $user->setUsername('committer');
        $user->setRealName('Committer User');
        $user->setEmail('committer@example.com');
        $user->setPlainPassword('committer');
        $user->addRole('ROLE_WATCHER');
        $user->addRole('ROLE_COMMITTER');
        $user->setEnabled(true);

        $userManager->updateUser($user);
        $this->addReference(self::COMMITTER, $user);

        $user = $userManager->createUser();
        $user->setUsername('admin');
        $user->setRealName('Admin User');
        $user->setEmail('admin@example.com');
        $user->setPlainPassword('admin');
        $user->addRole('ROLE_WATCHER');
        $user->addRole('ROLE_COMMITTER');
        $user->addRole('ROLE_ADMIN');
        $user->setEnabled(true);

        $userManager->updateUser($user);
        $this->addReference(self::ADMIN, $user);
    }
}
