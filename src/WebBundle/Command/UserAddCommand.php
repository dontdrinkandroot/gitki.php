<?php


namespace Dontdrinkandroot\Gitki\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class UserAddCommand extends AbstractUserCommand
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

        $user = $this->createUser($input, $output);

        $this->printUser($user, $output);

        $createQuestion = new ConfirmationQuestion('Create this user? ', false);
        if ($questionHelper->ask($input, $output, $createQuestion)) {
            $userManager->updateUser($user);
        }
    }
}
