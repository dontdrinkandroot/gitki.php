<?php


namespace Dontdrinkandroot\Gitki\WebBundle\Command;

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
        $users = $this->findUsers();
        $this->printUserTable($output, $users);
    }
}
