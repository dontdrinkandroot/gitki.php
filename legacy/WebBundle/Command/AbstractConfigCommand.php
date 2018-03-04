<?php


namespace Dontdrinkandroot\Gitki\WebBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;

abstract class AbstractConfigCommand extends Command
{
    /**
     * @return QuestionHelper
     */
    protected function getQuestionHelper()
    {
        return $this->getHelper('question');
    }

    protected function getRootDir()
    {
        return realpath(__DIR__ . '/../../../../github/');
    }
}
