<?php

namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserListCommand extends AbstractUserCommand
{
    protected function configure()
    {
        $this
            ->setName('gitki:user:list')
            ->setDescription('Lists the existing users');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $users = $this->findUsers();
        $this->printUserTable($output, $users);
    }
}
