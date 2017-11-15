<?php

namespace Dontdrinkandroot\Gitki\WebBundle\Command;

use Dontdrinkandroot\Gitki\WebBundle\Entity\User;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserAdminCheckCommand extends AbstractUserCommand
{
    protected function configure()
    {
        $this
            ->setName('gitki:user:admin-check')
            ->setDescription('Checks if a user with username "admin" is available or creates it');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userManager = $this->getUserManager();

        $user = $userManager->findUserByUsername('admin');
        if (null !== $user) {
            $output->writeln('Admin user already exists');

            return;
        }

        $password = bin2hex(random_bytes(16));
        /** @var User $user */
        $user = $userManager->createUser();
        $user->setUsername('admin');
        $user->setEmail('admin@example.com');
        $user->setRealName('Administration User');
        $user->addRole('ROLE_ADMIN');
        $user->setEnabled(true);
        $user->setPlainPassword($password);
        $userManager->updateUser($user);

        $output->writeln('Admin user created:');
        $this->printUser($user, $output);
        $output->writeln('Password: ' . $password);
    }
}
