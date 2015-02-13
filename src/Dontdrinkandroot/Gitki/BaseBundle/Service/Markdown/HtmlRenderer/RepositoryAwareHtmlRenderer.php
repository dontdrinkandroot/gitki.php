<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Service\Markdown\HtmlRenderer;

use Dontdrinkandroot\Gitki\BaseBundle\Repository\GitRepository;
use Net\Dontdrinkandroot\ObjectiveMarkdown\Renderer\Html\HtmlRenderer;
use Net\Dontdrinkandroot\Utils\Path\FilePath;

class RepositoryAwareHtmlRenderer extends HtmlRenderer
{

    /**
     * @var FilePath
     */
    private $path;

    /**
     * @var \Dontdrinkandroot\Gitki\BaseBundle\Repository\GitRepository
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
            'Net\Dontdrinkandroot\ObjectiveMarkdown\Model\Element\Inline\Link',
            $this->linkHandler
        );
        $this->context->registerHandler(
            'Net\Dontdrinkandroot\ObjectiveMarkdown\Model\Element\Block\Header',
            $this->headerHandler
        );
        $this->context->registerHandler(
            'Net\Dontdrinkandroot\ObjectiveMarkdown\Model\Element\Block\Table\Table',
            new BootstrapTableHandler()
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
