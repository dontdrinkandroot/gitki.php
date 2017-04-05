<?php


namespace Dontdrinkandroot\Gitki\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class CheckConfigCommand extends AbstractConfigCommand
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $securityConfig;

    protected function configure()
    {
        $this
            ->setName('gitki:config:check')
            ->setDescription('Check if the configuration is valid and configure mandatory parameters');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rootDir = $this->getRootDir();
        $configPath = $rootDir . '/app/config/config.yml';
        $securityConfigPath = $rootDir . '/app/config/security.yml';
        $this->config = Yaml::parse($configPath);
        $this->securityConfig = Yaml::parse($securityConfigPath);

//        $this->doConfigSteps($input, $output);

// TODO: continue
        $output->writeln(Yaml::dump($this->config, 10));
        file_put_contents($configPath, Yaml::dump($this->config, 10));
        $output->writeln(Yaml::dump($this->securityConfig, 10));
        file_put_contents($securityConfigPath, Yaml::dump($this->securityConfig, 10));
    }

//    protected function doConfigSteps(InputInterface $input, OutputInterface $output)
//    {
//        $this->configureRepositoryPath($input, $output);
//    }
//
//    protected function configureRepositoryPath($input, $output)
//    {
//        $repositoryPath = null;
//        if (isset($this->gitkiConfig['repository_path'])) {
//            $repositoryPath = $this->gitkiConfig['repository_path'];
//        }
//
//        $questionString = 'Please specify the git repository path';
//        if (null !== $repositoryPath) {
//            $questionString .= ' (or hit enter to use "' . $repositoryPath . '")';
//        }
//        $questionString .= ': ';
//        $question = new Question($questionString, $repositoryPath);
//        $this->gitkiConfig['repository_path'] = $this->getQuestionHelper()->ask($input, $output, $question);
//    }
}
