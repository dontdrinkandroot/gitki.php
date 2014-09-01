<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class EditUserCommand extends GitkiUsersCommand
{

    protected function configure()
    {
        $this
            ->setName('gitki:users:edit')
            ->setDescription('Edits an existing user');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userService = $this->getUserService();
        $users = $userService->listUsers();
        $questionHelper = $this->getQuestionHelper();

        $this->printUserTable($output, $users);

        $ids = array();
        foreach ($users as $user) {
            $ids[$user->getId()] = $user->getId();
        }
        var_dump($ids);
        $idQuestion = new ChoiceQuestion(
            'Id: ',
            $ids,
            null
        );
        $id = $questionHelper->ask($input, $output, $idQuestion);

        $user = $userService->findUserById($id);
        if (null === $user) {
            $output->writeln('User not found');

            return;
        }

        $this->editUser($input, $output, $user, $questionHelper, $userService);

        $this->printUser($user, $output);

        $saveQuestion = new ConfirmationQuestion('Save? ', false);
        if ($questionHelper->ask($input, $output, $saveQuestion)) {
            $userService->saveUser($user);
        }
    }
}