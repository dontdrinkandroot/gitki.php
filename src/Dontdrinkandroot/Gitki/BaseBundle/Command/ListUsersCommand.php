<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Command;

use Dontdrinkandroot\Gitki\BaseBundle\Entity\User;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListUsersCommand extends GitkiUsersCommand
{

    protected function configure()
    {
        $this
            ->setName('gitki:user:list')
            ->setDescription('Lists the existing users');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userManager = $this->getUserManager();
        /** @var User[] $users */
        $users = $userManager->findUsers();

        $this->printUserTable($output, $users);
    }
}
