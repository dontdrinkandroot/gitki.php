<?php


namespace Dontdrinkandroot\Gitki\WebBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ChangeConfigCommand extends AbstractConfigCommand
{

    /**
     * @var array
     */
    protected $gitkiConfig;

    protected function configure()
    {
        $this
            ->setName('gitki:config:change')
            ->setDescription('Change the configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
