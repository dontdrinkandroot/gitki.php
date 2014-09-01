<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Command;

use Net\Dontdrinkandroot\Gitki\BaseBundle\Entity\User;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Service\UserService;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

abstract class GitkiUsersCommand extends GitkiContainerAwareCommand
{

    protected function printUser(User $user, OutputInterface $output)
    {
        $output->writeln('--------------------');
        $output->writeln('Real Name: ' . $user->getRealName());
        $output->writeln('Email: ' . $user->getEmail());
        $output->writeln('Roles: ' . implode(',', $user->getRoles()));
        $output->writeln('Login: ' . $user->getLogin());
        $output->writeln('Github Login: ' . $user->getGithubLogin());
        $output->writeln('Google Login: ' . $user->getGoogleLogin());
        $output->writeln('--------------------');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param User            $user
     * @param QuestionHelper  $questionHelper
     * @param UserService     $userService
     *
     * @return mixed
     */
    protected function editUser(
        InputInterface $input,
        OutputInterface $output,
        User $user,
        QuestionHelper $questionHelper,
        UserService $userService
    ) {
        $realNameQuestion = new Question('Real Name [required]: ');
        $realNameQuestion->setValidator(
            function ($answer) {
                if (empty($answer)) {
                    throw new \RuntimeException('Real Name must not be empty');
                }

                return $answer;
            }
        );
        $realNameQuestion->setMaxAttempts(2);
        $user->setRealName($questionHelper->ask($input, $output, $realNameQuestion));

        $emailQuestion = new Question('Email [required]: ');
        $emailQuestion->setValidator(
            function ($answer) {
                if (!filter_var($answer, FILTER_VALIDATE_EMAIL)) {
                    throw new \RuntimeException("This is not a valid email address");
                }

                return $answer;
            }
        );
        $emailQuestion->setMaxAttempts(2);
        $user->setEmail($questionHelper->ask($input, $output, $emailQuestion));

        $roleQuestion = new ChoiceQuestion(
            'Role [required]: ',
            array('ROLE_WATCHER', 'ROLE_COMMITTER', 'ROLE_ADMIN'),
            null
        );
        $user->setRoles([$questionHelper->ask($input, $output, $roleQuestion)]);

        $loginQuestion = new Question('Login: ');
        $login = $questionHelper->ask($input, $output, $loginQuestion);
        if (null !== $login) {
            $passwordQuestion = new Question('Password: ');
            $passwordQuestion->setHidden(true);
            $passwordQuestion->setHiddenFallback(false);
            $passwordQuestion->setMaxAttempts(3);
            $passwordQuestion->setValidator(
                function ($answer) {
                    if (empty($answer)) {
                        throw new \RuntimeException("Password must not be empty");
                    }

                    // TODO: More password validation here.
                    return $answer;
                }
            );
            $password = $questionHelper->ask($input, $output, $passwordQuestion);

            $user->setLogin($login);
            $user = $userService->changePassword($user, $password);
        } else {
            $user->setLogin(null);
            $user->setPassword(null);
        }

        $githubLoginQuestion = new Question('Github Login: ');
        $user->setGithubLogin($questionHelper->ask($input, $output, $githubLoginQuestion));

        $googleLoginQuestion = new Question('Google Login: ');
        $user->setGoogleLogin($questionHelper->ask($input, $output, $googleLoginQuestion));

        return $user;
    }

    /**
     * @param OutputInterface $output
     * @param User[]          $users
     */
    protected function printUserTable(OutputInterface $output, $users)
    {
        $table = new Table($output);
        $table->setHeaders(['Id', 'Real Name', 'Email', 'Roles', 'Login', 'GitHub Login', 'Google Login']);

        foreach ($users as $user) {
            $table->addRow(
                [
                    $user->getId(),
                    $user->getRealName(),
                    $user->getEmail(),
                    implode(',', $user->getRoles()),
                    $user->getLogin(),
                    $user->getGithubLogin(),
                    $user->getGoogleLogin()
                ]
            );
        }

        $table->render();
    }
} 