<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SearchCommand extends ElasticsearchCommand
{

    protected function configure()
    {
        $this->setName('gitki:search')
            ->setDescription('Search for Markdown documents')
            ->addArgument('searchstring', InputArgument::REQUIRED, 'The search string');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $searchString = $input->getArgument('searchstring');
        $elasticSearchRepo = $this->getElasticsearchRepository();
        $results = $elasticSearchRepo->search($searchString);
        if (count($results) == 0) {
            $output->writeln('No results found');
        } else {
            foreach ($results as $result) {
                $output->writeln(
                    '[' . $result->getScore() . '] ' . $result->getTitle() . ' (' . $result->getPath(
                    )->toAbsoluteString() . ')'
                );
            }
        }
    }
}
