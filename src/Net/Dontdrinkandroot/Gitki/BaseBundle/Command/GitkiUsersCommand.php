<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Command;

use FOS\UserBundle\Model\UserManagerInterface;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Entity\User;
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
        $output->writeln('Id: ' . $user->getId());
        $output->writeln('User Name: ' . $user->getUsername());
        $output->writeln('Email: ' . $user->getEmail());
        $output->writeln('Roles: ' . implode(',', $user->getRoles()));
        $output->writeln('Github Login: ' . $user->getGithubId());
        $output->writeln('Google Login: ' . $user->getGoogleId());
        $output->writeln('--------------------');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param User            $user
     * @param QuestionHelper  $questionHelper
     * @param UserManagerInterface $userManager
     *
     * @return mixed
     */
    protected function editUser(
        InputInterface $input,
        OutputInterface $output,
        User $user,
        QuestionHelper $questionHelper,
        UserManagerInterface $userManager
    ) {
        $user = $this->editRealName($input, $output, $user, $questionHelper);
        $user = $this->editEmail($input, $output, $user, $questionHelper);
        $user = $this->editRole($input, $output, $user, $questionHelper);
        $user = $this->editPassword($input, $output, $user, $questionHelper, $userManager);
        $user = $this->editGithubId($input, $output, $user, $questionHelper);
        $user = $this->editGoogleId($input, $output, $user, $questionHelper);

        return $user;
    }

    /**
     * @param OutputInterface $output
     * @param User[]          $users
     */
    protected function printUserTable(OutputInterface $output, $users)
    {
        $table = new Table($output);
        $table->setHeaders(['ID', 'User Name', 'Email', 'Roles', 'GitHub ID', 'Google ID']);

        foreach ($users as $user) {
            $table->addRow(
                [
                    $user->getId(),
                    $user->getUsername(),
                    $user->getEmail(),
                    implode(',', $user->getRoles()),
                    $user->getGithubId(),
                    $user->getGoogleId()
                ]
            );
        }

        $table->render();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param User            $user
     * @param QuestionHelper  $questionHelper
     *
     * @return User
     */
    protected function editRealName(
        InputInterface $input,
        OutputInterface $output,
        User $user,
        QuestionHelper $questionHelper
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
        $user->setUsername($questionHelper->ask($input, $output, $realNameQuestion));

        return $user;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param User            $user
     * @param QuestionHelper  $questionHelper
     *
     * @return User
     */
    protected function editEmail(
        InputInterface $input,
        OutputInterface $output,
        User $user,
        QuestionHelper $questionHelper
    ) {
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

        return $user;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param User            $user
     * @param QuestionHelper  $questionHelper
     *
     * @return User
     */
    protected function editRole(
        InputInterface $input,
        OutputInterface $output,
        User $user,
        QuestionHelper $questionHelper
    ) {
        $roleQuestion = new ChoiceQuestion(
            'Role [required]: ',
            array('ROLE_WATCHER', 'ROLE_COMMITTER', 'ROLE_ADMIN'),
            null
        );
        $user->setRoles([$questionHelper->ask($input, $output, $roleQuestion)]);

        return $user;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param User            $user
     * @param QuestionHelper  $questionHelper
     * @param UserManagerInterface $userManager
     *
     * @return User
     */
    protected function editPassword(
    InputInterface $input,
        OutputInterface $output,
        User $user,
        QuestionHelper $questionHelper,
        UserManagerInterface $userManager
    ) {
        $passwordQuestion = new Question('Password (leave blank to disable form login): ');
        $passwordQuestion->setHidden(true);
        $passwordQuestion->setHiddenFallback(false);
        $passwordQuestion->setMaxAttempts(3);
        $password = $questionHelper->ask($input, $output, $passwordQuestion);

        if (null !== $password) {
            $user->setPassword($password);
            $userManager->updatePassword($user);
        } else {
            $user->setPassword(null);
        }

        return $user;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param User            $user
     * @param QuestionHelper  $questionHelper
     *
     * @return User
     */
    protected function editGithubId(
    InputInterface $input,
        OutputInterface $output,
        User $user,
        QuestionHelper $questionHelper
    ) {
        $githubLoginQuestion = new Question('Github ID: ');
        $user->setGithubId($questionHelper->ask($input, $output, $githubLoginQuestion));

        return $user;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param User            $user
     * @param QuestionHelper  $questionHelper
     *
     * @return User
     */
    protected function editGoogleId(
    InputInterface $input,
        OutputInterface $output,
        User $user,
        QuestionHelper $questionHelper
    ) {
        $googleLoginQuestion = new Question('Google ID: ');
        $user->setGoogleId($questionHelper->ask($input, $output, $googleLoginQuestion));

        return $user;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param User[]          $users
     * @param QuestionHelper  $questionHelper
     *
     * @return User
     */
    protected function selectUser(InputInterface $input, OutputInterface $output, $users, $questionHelper)
    {
        $userChoices = array();
        foreach ($users as $user) {
            $userChoices[$user->getId()] = $user;
        }
        $idQuestion = new ChoiceQuestion(
            'Select User: ',
            $userChoices,
            null
        );
        $user = $questionHelper->ask($input, $output, $idQuestion);

        return $user;
    }
} 