<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Command;

use Dontdrinkandroot\Gitki\BaseBundle\Entity\User;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class AddUserCommand extends GitkiUsersCommand
{
    protected function configure()
    {
        $this
            ->setName('gitki:user:add')
            ->setDescription('Add a new user');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();
        $userManager = $this->getUserManager();

        /** @var User $user */
        $user = $userManager->createUser();
        $user->setEnabled(true);

        $user = $this->editUser($input, $output, $user, $questionHelper, $userManager);

        $this->printUser($user, $output);

        $createQuestion = new ConfirmationQuestion('Create this user? ', false);
        if ($questionHelper->ask($input, $output, $createQuestion)) {
            $userManager->updateUser($user);
        }
    }
}