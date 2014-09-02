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
            ->setName('gitki:user:edit')
            ->setDescription('Edits an existing user');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userService = $this->getUserService();
        $users = $userService->listUsers();
        $questionHelper = $this->getQuestionHelper();

        $user = $this->selectUser($input, $output, $users, $questionHelper);

        $fields = ['Real Name', 'Email', 'Role', 'Login and Password', 'Github Login', 'Google Login', 'Done'];

        $fieldQuestion = new ChoiceQuestion(
            'Select Field: ',
            $fields
        );
        $field = 'Done';
        do {
            $this->printUser($user, $output);
            $field = $questionHelper->ask($input, $output, $fieldQuestion);
            switch ($field) {
                case 'Real Name':
                    $user = $this->editRealName($input, $output, $user, $questionHelper);
                    break;
                case 'Email':
                    $user = $this->editEmail($input, $output, $user, $questionHelper);
                    break;
                case 'Role':
                    $user = $this->editRole($input, $output, $user, $questionHelper);
                    break;
                case 'Login and Password':
                    $user = $this->editLoginAndPassword($input, $output, $user, $questionHelper, $userService);
                    break;
                case 'Github Login':
                    $user = $this->editGithubLogin($input, $output, $user, $questionHelper);
                    break;
                case 'Google Login':
                    $user = $this->editGoogleLogin($input, $output, $user, $questionHelper);
                    break;
            }
        } while ('Done' !== $field);

        $this->printUser($user, $output);

        $saveQuestion = new ConfirmationQuestion('Save? ', false);
        if ($questionHelper->ask($input, $output, $saveQuestion)) {
            $userService->saveUser($user);
        }
    }
}