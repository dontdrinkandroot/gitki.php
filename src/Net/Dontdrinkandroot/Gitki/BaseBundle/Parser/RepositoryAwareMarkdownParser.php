<?php
namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Parser;

use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\ParsedMarkdownDocument;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\Path\FilePath;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\Path\Path;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Repository\GitRepository;
use Net\Dontdrinkandroot\Symfony\ExtensionBundle\Utils\StringUtils;

class RepositoryAwareMarkdownParser extends \Parsedown implements MarkdownParser
{

    /**
     * @var FilePath|null
     */
    private $path = null;

    /**
     * @var GitRepository
     */
    private $gitRepository;

    /**
     * @var string
     */
    private $title;

    /**
     * @var array
     */
    private $toc;

    /**
     * @var Path[]
     */
    private $linkedPaths;

    private $currentH2;

    private $headingCount;

    public function __construct(FilePath $path, GitRepository $gitRepository)
    {
        $this->gitRepository = $gitRepository;
        $this->path = $path;
        $this->linkedPaths = array();
        $this->currentH2 = null;
        $this->headingCount = 0;
    }

    /**
     * @inheritdoc
     */
    public function parse($content)
    {
        $html = parent::text($content);
        if (null !== $this->currentH2) {
            $this->toc[] = $this->currentH2;
        }

        $parsed = new ParsedMarkdownDocument();
        $parsed->setTitle($this->title);
        $parsed->setSource($content);
        $parsed->setHtml($html);
        $parsed->setLinkedPaths($this->linkedPaths);
        $parsed->setToc($this->toc);

        return $parsed;
    }

    protected function element(array $element)
    {
        switch ($element['name']) {
            case "h1":
                return $this->handleHeading1($element);
            case "h2":
                return $this->handleHeading2($element);
            case "h3":
                return $this->handleHeading3($element);
            case 'a':
                return $this->handleAnchor($element);
            default:
                return parent::element($element);
        }
    }

    protected function handleHeading($element)
    {
        $this->headingCount++;

        if (!isset($element['attributes']['id'])) {
            if (!isset($element['attributes'])) {
                $element['attributes'] = array();
            }
            $element['attributes']['id'] = 'heading' . $this->headingCount;
        }

        return $element;
    }

    protected function handleHeading1($element)
    {
        $element = $this->handleHeading($element);

        if (null === $this->title) {
            $this->title = $element['text'];
        }
        if (null !== $this->currentH2) {
            $this->toc[] = $this->currentH2;
            $this->currentH2 = null;
        }

        return parent::element($element);
    }

    protected function handleHeading2($element)
    {
        if (null !== $this->currentH2) {
            $this->toc[] = $this->currentH2;
        }

        $element = $this->handleHeading($element);

        $this->currentH2 = array(
            'text' => $element['text'],
            'id' => $element['attributes']['id'],
            'children' => array()
        );

        return parent::element($element);
    }

    protected function handleHeading3($element)
    {
        $element = $this->handleHeading($element);

        if (null !== $this->currentH2) {
            $this->currentH2['children'][] = array(
                'text' => $element['text'],
                'id' => $element['attributes']['id'],
            );
        }

        return parent::element($element);
    }

    protected function handleAnchor($element)
    {
        $url = $element['attributes']['href'];
        if ($this->isExternalUrl($url)) {
            $element['attributes']['rel'] = 'external';
        } else {
            if (!$this->targetUrlExists($url)) {
                $element['attributes']['class'] = 'missing';
            }
        }

        return parent::element($element);
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
                $path = $this->path->getParentPath()->appendPathString($urlPath);
            }

            $fileExists = $this->gitRepository->exists($path);

            $this->linkedPaths[] = $path;

            return $fileExists;

        } catch (\Exception $e) {
        }

        return true;
    }

}