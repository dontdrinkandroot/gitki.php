<?php

namespace Dontdrinkandroot\Gitki\WebBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Users implements FixtureInterface, ContainerAwareInterface
{

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

        $user = $userManager->createUser();
        $user->setUsername('watcher');
        $user->setEmail('watcher@example.com');
        $user->setPlainPassword('watcher');
        $user->setEnabled(true);

        $userManager->updateUser($user);

        $user = $userManager->createUser();
        $user->setUsername('committer');
        $user->setEmail('committer@example.com');
        $user->setPlainPassword('committer');
        $user->setEnabled(true);

        $userManager->updateUser($user);

        $user = $userManager->createUser();
        $user->setUsername('admin');
        $user->setEmail('admin@example.com');
        $user->setPlainPassword('admin');
        $user->setEnabled(true);

        $userManager->updateUser($user);
    }
}
