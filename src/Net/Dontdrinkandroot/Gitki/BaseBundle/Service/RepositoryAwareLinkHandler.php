<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Service;


use Net\Dontdrinkandroot\Gitki\BaseBundle\Repository\GitRepository;
use Net\Dontdrinkandroot\ObjectiveMarkdown\Model\Span\Link;
use Net\Dontdrinkandroot\ObjectiveMarkdown\Renderer\Html\Handler\LinkHandler;
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

    /**
     * @param Link $element
     * @return string
     */
    protected function renderAttributes($element)
    {
        $attributes = '';

        $classString = '';
        if ($externalUrl = $this->isExternalUrl($element->getUrl())) {
            $classString .= 'external';
        } else {
            if (!$this->targetUrlExists($element->getUrl())) {
                $classString .= 'missing';
            }
        }

        if (null !== $element->getUrl()) {
            $attributes .= ' href="' . $element->getUrl() . '"';
        }

        if (null !== $element->getTitle()) {
            $attributes .= ' title="' . $element->getTitle() . '"';
        }

        $attributes .= ' class="' . $classString . '"';

        return $attributes;
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