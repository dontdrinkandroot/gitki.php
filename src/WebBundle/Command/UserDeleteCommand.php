<?php


namespace Dontdrinkandroot\Gitki\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class UserDeleteCommand extends AbstractUserCommand
{
    protected function configure()
    {
        $this
            ->setName('gitki:user:delete')
            ->setDescription('Deletes an existing user');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = $this->selectUser($input, $output);

        $this->printUser($user, $output);

        $saveQuestion = new ConfirmationQuestion('Are you sure you want to delete this user? ', false);
        if ($this->getQuestionHelper()->ask($input, $output, $saveQuestion)) {
            $this->getUserManager()->deleteUser($user);
        }
    }
}
