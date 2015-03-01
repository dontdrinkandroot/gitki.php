<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Command;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReindexCommand extends ElasticsearchCommand
{

    protected function configure()
    {
        $this->setName('gitki:reindex')
            ->setDescription('Reindex all Markdown documents');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $wikiService = $this->getWikiService();
        $elasticSearchRepo = $this->getElasticsearchRepository();

        $elasticSearchRepo->clear();

        $filePaths = $wikiService->findAllFiles();

        $progress = new ProgressBar($output, count($filePaths));
        $progress->start();

        foreach ($filePaths as $filePath) {
            $progress->setMessage('Indexing ' . $filePath->toAbsoluteString());
            $progress->advance();

            $elasticSearchRepo->addFile($filePath);
        }

        $progress->finish();

        $output->writeln('');
    }
}
