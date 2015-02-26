<?php


namespace Dontdrinkandroot\Gitki\MarkdownBundle\Service;

use Dontdrinkandroot\Gitki\BaseBundle\Model\ParsedMarkdownDocument;
use Dontdrinkandroot\Gitki\BaseBundle\Repository\GitRepository;
use Dontdrinkandroot\Gitki\MarkdownBundle\Renderer\RepositoryAwareLinkRenderer;
use Dontdrinkandroot\Gitki\MarkdownBundle\Renderer\TocBuildingHeaderRenderer;
use Dontdrinkandroot\Gitki\MarkdownBundle\Service\MarkdownService;
use Dontdrinkandroot\Path\FilePath;
use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use League\CommonMark\HtmlRenderer;

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

        $linkRenderer = new RepositoryAwareLinkRenderer($path, $this->repository);
        $headerRenderer = new TocBuildingHeaderRenderer();

        $environment = Environment::createCommonMarkEnvironment();
        $environment->addInlineRenderer('League\CommonMark\Inline\Element\Link', $linkRenderer);
        $environment->addBlockRenderer('League\CommonMark\Block\Element\Header', $headerRenderer);

        $parser = new DocParser($environment);
        $htmlRenderer = new HtmlRenderer($environment);
        $documentAST = $parser->parse($content);
        $html = $htmlRenderer->renderBlock($documentAST);

        $linkedPaths = $linkRenderer->getLinkedPaths();
        $title = $headerRenderer->getTitle();
        $toc = $headerRenderer->getToc();

        $result = new ParsedMarkdownDocument();
        $result->setSource($content);
        $result->setLinkedPaths($linkedPaths);
        $result->setTitle($title);
        $result->setToc($toc);
        $result->setHtml($html);

        return $result;
    }
}
