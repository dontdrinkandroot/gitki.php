<?php


namespace Dontdrinkandroot\Gitki\WebBundle\Command;

use GitWrapper\GitWrapper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;

class LoadFixturesCommand extends ContainerAwareCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('gitki:fixtures:load')
            ->setDescription('Load fixtures as they are used in the tests');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrineFixturesCommand = $this->getApplication()->find('doctrine:fixtures:load');
        $doctrineFixturesCommand->run(new ArrayInput([]), $output);

        $questionHelper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Overwrite repository?', false);
        if (!$questionHelper->ask($input, $output, $question)) {
            return;
        }

        $repositoryPath = $this->getContainer()->getParameter('repository_path');
        $testRepoPath = realPath(__DIR__ . '/../../../vendor/dontdrinkandroot/gitki-bundle/Tests/Data/repo/');

        $fileSystem = new Filesystem();
        $fileSystem->remove($repositoryPath);

        $fileSystem->remove($repositoryPath);

        $fileSystem->mkdir($repositoryPath);
        $fileSystem->mirror($testRepoPath, $repositoryPath);

        $git = new GitWrapper();
        $workingCopy = $git->init($repositoryPath);
        $workingCopy->config('user.email', 'gitki@example.com');
        $workingCopy->config('user.name', 'Gitki');
        $workingCopy->add('', ['A' => '']);
        $workingCopy->commit('Initial commit');
    }
}
