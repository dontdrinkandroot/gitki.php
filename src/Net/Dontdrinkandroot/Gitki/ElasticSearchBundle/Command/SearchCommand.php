<?php


namespace Net\Dontdrinkandroot\Gitki\ElasticSearchBundle\Command;


use Net\Dontdrinkandroot\Gitki\ElasticSearchBundle\Repository\ElasticSearchRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SearchCommand extends ElasticSearchCommand
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
        /* @var ElasticSearchRepository $elasticSearchRepo */
        $elasticSearchRepo = $this->getContainer()->get('ddr.gitki.repository.elasticsearch');
        $results = $elasticSearchRepo->searchMarkdownDocuments($searchString);
        if (count($results) == 0) {
            $output->writeln('No results found');
        } else {
            foreach ($results as $result) {
                $output->writeln(
                    '[' . $result->getScore() . '] ' . $result->getTitle() . ' (' . $result->getPath()->toUrlString(
                    ) . ')'
                );
            }
        }
    }


} 