<?php

namespace Dontdrinkandroot\Gitki\MarkdownBundle\Renderer;

use Dontdrinkandroot\Gitki\BaseBundle\Repository\GitRepository;
use League\CommonMark\HtmlElement;
use League\CommonMark\HtmlRenderer;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Renderer\LinkRenderer;
use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Utils\StringUtils;

class RepositoryAwareLinkRenderer extends LinkRenderer
{

    /**
     * @var GitRepository
     */
    private $gitRepository;

    private $linkedPaths = [];

    /**
     * @var FilePath
     */
    private $currentFilePath;

    public function __construct(FilePath $currentFilePath, GitRepository $gitRepository)
    {
        $this->gitRepository = $gitRepository;
        $this->currentFilePath = $currentFilePath;
    }

    /**
     * @param Link         $inline
     * @param HtmlRenderer $htmlRenderer
     *
     * @return HtmlElement
     */
    public function render(AbstractInline $inline, HtmlRenderer $htmlRenderer)
    {
        $htmlElement = parent::render($inline, $htmlRenderer);

        if ($externalUrl = $this->isExternalUrl($inline->getUrl())) {
            $htmlElement->setAttribute('rel', 'external');
        } else {
            if (!$this->targetUrlExists($inline->getUrl())) {
                $classes = $htmlElement->getAttribute('class');
                if (null === $classes) {
                    $classes = '';
                } else {
                    $classes .= ' ';
                }
                $classes .= 'missing';
                $htmlElement->setAttribute('class', $classes);
            }
        }

        return $htmlElement;
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
                $currentDirectoryPath = $this->currentFilePath->getParentPath();
                $path = $currentDirectoryPath->appendPathString($urlPath);
            }

            $fileExists = $this->gitRepository->exists($path);

            $this->linkedPaths[] = $path;

            return $fileExists;
        } catch (\Exception $e) {
        }

        return true;
    }

    /**
     * @return array
     */
    public function getLinkedPaths()
    {
        return $this->linkedPaths;
    }
}
