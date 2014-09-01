<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Command;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListUsersCommand extends GitkiContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('gitki:users:list')
            ->setDescription('Lists the existing users');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userService = $this->getUserService();
        $users = $userService->listUsers();

        $table = new Table($output);
        $table->setHeaders(['Id', 'Real Name', 'Email', 'Roles', 'Login', 'GitHub Login', 'Google Login']);

        var_dump($users);
        foreach ($users as $user) {
            $table->addRow(
                [
                    $user->getId(),
                    $user->getRealName(),
                    $user->getEmail(),
                    $user->getRoles(),
                    $user->getGithubLogin(),
                    $user->getGoogleLogin()
                ]
            );
        }

        $table->render();
    }
}