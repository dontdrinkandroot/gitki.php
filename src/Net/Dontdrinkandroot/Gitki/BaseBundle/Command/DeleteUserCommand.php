<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class DeleteUserCommand extends GitkiUsersCommand
{

    protected function configure()
    {
        $this
            ->setName('gitki:user:delete')
            ->setDescription('Deletes an existing user');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userManager = $this->getUserManager();
        $users = $userManager->findUsers();
        $questionHelper = $this->getQuestionHelper();

        $user = $this->selectUser($input, $output, $users, $questionHelper);

        $this->printUser($user, $output);

        $saveQuestion = new ConfirmationQuestion('Are you sure you want to delete this user? ', false);
        if ($questionHelper->ask($input, $output, $saveQuestion)) {
            $userManager->deleteUser($user);
        }
    }
}