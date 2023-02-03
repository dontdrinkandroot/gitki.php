<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class Users extends Fixture implements DependentFixtureInterface
{
    /** @deprecated */
    final public const WATCHER = 'user_watcher';
    /** @deprecated */
    final public const COMMITTER = 'user_committer';
    /** @deprecated */
    final public const ADMIN = 'user_admin';

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager): void
    {
        /* Noop */
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            UserWatcher::class,
            UserCommitter::class,
            UserAdmin::class
        ];
    }
}
