<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Service;


use Net\Dontdrinkandroot\Gitki\BaseBundle\Repository\GitRepository;
use Net\Dontdrinkandroot\ObjectiveMarkdown\Renderer\Html\HtmlRenderer;
use Net\Dontdrinkandroot\Utils\Path\FilePath;

class RepositoryAwareHtmlRenderer extends HtmlRenderer
{

    /**
     * @var FilePath
     */
    private $path;

    /**
     * @var \Net\Dontdrinkandroot\Gitki\BaseBundle\Repository\GitRepository
     */
    private $repository;

    private $linkHandler;

    public function __construct(FilePath $path, GitRepository $repository)
    {
        parent::__construct();
        $this->path = $path;
        $this->repository = $repository;
        $this->linkHandler = new RepositoryAwareLinkHandler($this->path, $this->repository);
        $this->headerHandler = new TocBuildingHeaderHandler();
    }

    protected function getContext()
    {
        $this->context->registerHandler(
            'Net\Dontdrinkandroot\ObjectiveMarkdown\Model\Span\Link',
            $this->linkHandler
        );
        $this->context->registerHandler(
            'Net\Dontdrinkandroot\ObjectiveMarkdown\Model\Block\Header',
            $this->headerHandler
        );

        return $this->context;
    }

    /**
     * @return RepositoryAwareLinkHandler
     */
    public function getLinkHandler()
    {
        return $this->linkHandler;
    }

    /**
     * @return TocBuildingHeaderHandler
     */
    public function getHeaderHandler()
    {
        return $this->headerHandler;
    }

} 