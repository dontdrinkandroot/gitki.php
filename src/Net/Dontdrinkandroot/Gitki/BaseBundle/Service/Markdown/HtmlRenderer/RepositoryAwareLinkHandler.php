<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Service\Markdown\HtmlRenderer;


use Net\Dontdrinkandroot\Gitki\BaseBundle\Repository\GitRepository;
use Net\Dontdrinkandroot\ObjectiveMarkdown\Model\General\HtmlMarkup;
use Net\Dontdrinkandroot\ObjectiveMarkdown\Model\Span\Link;
use Net\Dontdrinkandroot\ObjectiveMarkdown\Renderer\Html\Handler\LinkHandler;
use Net\Dontdrinkandroot\ObjectiveMarkdown\Renderer\Html\HtmlRenderContext;
use Net\Dontdrinkandroot\Utils\Path\FilePath;
use Net\Dontdrinkandroot\Utils\StringUtils;

class RepositoryAwareLinkHandler extends LinkHandler
{

    /**
     * @var FilePath
     */
    private $path;

    /**
     * @var GitRepository
     */
    private $repository;

    private $linkedPaths = array();

    public function __construct(FilePath $path, GitRepository $repository)
    {
        parent::__construct();
        $this->path = $path;
        $this->repository = $repository;
    }

    public function getLinkedPaths()
    {
        return $this->linkedPaths;
    }

    protected function createAttributes($element)
    {
        $attributes = parent::createAttributes($element);
        if ($externalUrl = $this->isExternalUrl($element->getUrl())) {
            $attributes['rel'] = 'external';
        } else {
            if (!$this->targetUrlExists($element->getUrl())) {
                $attributes['class'] = 'missing';
            }
        }

        return $attributes;
    }

    /**
     * @param Link              $element
     * @param HtmlRenderContext $context
     *
     * @return string
     */
    protected function renderChildren($element, HtmlRenderContext $context)
    {
        $externalUrl = $this->isExternalUrl($element->getUrl());
        $markup = '';
        if ($externalUrl) {
            $iconElement = new HtmlMarkup('<span class="fa fa-external-link-square"></span> ');
            $markup .= $this->handleChild($iconElement, $context);
        }
        $markup .= parent::renderChildren($element, $context);

        return $markup;
    }

    protected function isExternalUrl($url)
    {
        try {
            $urlParts = parse_url($url);
            if (array_key_exists('scheme', $urlParts)) {
                return true;
            }
            if (array_key_exists('host', $urlParts)) {
                return true;
            }
        } catch (\Exception $e) {
        }

        return false;
    }

    protected function targetUrlExists($url)
    {
        try {
            $urlParts = parse_url($url);

            $urlPath = $urlParts['path'];
            $path = null;
            if (StringUtils::startsWith($urlPath, '/')) {
                /* Absolute paths won't work */
                return false;
            } else {
                $directoryPath = $this->path->getParentPath();
                $path = $directoryPath->appendPathString($urlPath);
            }

            $fileExists = $this->repository->exists($path);

            $this->linkedPaths[] = $path;

            return $fileExists;

        } catch (\Exception $e) {
        }

        return true;
    }


}