<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class InstallCommand extends GitkiContainerAwareCommand
{

    /**
     * @var array
     */
    protected $gitkiConfig;

    protected function configure()
    {
        $this
            ->setName('gitki:install')
            ->setDescription('Checks and builds the required installation parameters');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Application $application */
        $application = $this->getApplication();
        $rootDir = $application->getKernel()->getRootDir();
        $gitKiConfigPath = $rootDir . '/config/gitki.yml';
        $config = Yaml::parse($gitKiConfigPath);
        if (isset($config['ddr_gitki'])) {
            $this->gitkiConfig = $config['ddr_gitki'];
        } else {
            $this->gitkiConfig = array();
        }

        $this->doConfigSteps($input, $output);

        var_dump($this->gitkiConfig);
        var_dump(Yaml::dump($this->gitkiConfig, 10));
    }

    protected function doConfigSteps(InputInterface $input, OutputInterface $output)
    {
        $this->configureRepositoryPath($input, $output);
    }

    protected function configureRepositoryPath($input, $output)
    {
        $repositoryPath = null;
        if (isset($this->gitkiConfig['repository_path'])) {
            $repositoryPath = $this->gitkiConfig['repository_path'];
        }

        $questionString = 'Please specify the git repository path';
        if (null !== $repositoryPath) {
            $questionString .= ' (or hit enter to use "' . $repositoryPath . '")';
        }
        $questionString .= ': ';
        $question = new Question($questionString, $repositoryPath);
        $this->gitkiConfig['repository_path'] = $this->getQuestionHelper()->ask($input, $output, $question);
    }

    /**
     * @return QuestionHelper
     */
    protected function getQuestionHelper()
    {
        return $this->getHelper('question');
    }
}