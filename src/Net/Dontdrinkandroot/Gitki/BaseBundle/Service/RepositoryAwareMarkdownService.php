<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Service;


use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\ParsedMarkdownDocument;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Repository\GitRepository;
use Net\Dontdrinkandroot\ObjectiveMarkdown\Parser\Parser;
use Net\Dontdrinkandroot\Utils\Path\FilePath;

class RepositoryAwareMarkdownService implements MarkdownService
{

    /**
     * @var GitRepository
     */
    protected $repository;

    /**
     * @param GitRepository $repository
     */
    public function __construct(GitRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param FilePath $path
     * @param string   $content
     *
     * @return ParsedMarkdownDocument
     */
    public function parse(FilePath $path, $content)
    {
        $parser = new Parser();
        $markdownDocument = $parser->parse($content);

        $renderer = new RepositoryAwareHtmlRenderer($path, $this->repository);
        $html = $renderer->render($markdownDocument);

        $linkedPaths = $renderer->getLinkHandler()->getLinkedPaths();
        $title = $renderer->getHeaderHandler()->getTitle();
        $toc = $renderer->getHeaderHandler()->getToc();

        $result = new ParsedMarkdownDocument();
        $result->setSource($content);
        $result->setLinkedPaths($linkedPaths);
        $result->setTitle($title);
        $result->setToc($toc);
        $result->setHtml($html);

        return $result;
    }

} 