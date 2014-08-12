<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Service;


use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\ParsedMarkdownDocument;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\Path\FilePath;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Parser\RepositoryAwareMarkdownParser;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Repository\GitRepository;
use Net\Dontdrinkandroot\ObjectiveMarkdown\Parser\Parser;

class RepositoryAwareMarkdownService implements MarkdownService
{

    protected $repository;

    public function __construct(GitRepository $repository)
    {
        $this->repository = $repository;
    }

    public function parse(FilePath $path, $content)
    {
        $parser = new Parser();
        $markdownDocument = $parser->parse($content);

        $renderer = new RepositoryAwareHtmlRenderer($path, $this->repository);
        $html = $renderer->render($markdownDocument);

        $result = new ParsedMarkdownDocument();
        $result->setLinkedPaths($renderer->getLinkHandler()->getLinkedPaths());
        $result->setTitle($renderer->getHeaderHandler()->getTitle());
        $result->setToc($renderer->getHeaderHandler()->getToc());
        $result->setHtml($html);

        return $result;
    }

} 