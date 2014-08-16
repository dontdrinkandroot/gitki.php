<?php


namespace Net\Dontdrinkandroot\Gitki\ElasticsearchBundle\Command;


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
        $markdownService = $this->getMarkdownService();

        $elasticSearchRepo->deleteMarkdownDocumentIndex();

        $filePaths = $wikiService->findAllMarkdownFiles();

        $progress = new ProgressBar($output, count($filePaths));
        $progress->start();

        foreach ($filePaths as $filePath) {
            $progress->setMessage('Indexing ' . $filePath->toAbsoluteUrlString());
            $progress->advance();

            $fileContent = $wikiService->getContent($filePath);
            $document = $markdownService->parse($filePath, $fileContent);
            $elasticSearchRepo->indexMarkdownDocument($filePath, $document);
        }

        $progress->finish();

        $output->writeln('');
    }

} 