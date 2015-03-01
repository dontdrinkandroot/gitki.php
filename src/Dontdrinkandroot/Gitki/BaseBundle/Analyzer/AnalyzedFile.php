<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Analyzer;

use Dontdrinkandroot\Path\Path;

class AnalyzedFile
{

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var Path[]
     */
    protected $linkedPaths = [];

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return null
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return null
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return Path[]
     */
    public function getLinkedPaths()
    {
        return $this->linkedPaths;
    }

    /**
     * @param Path[] $linkedPaths
     *
     * @return null
     */
    public function setLinkedPaths($linkedPaths)
    {
        $this->linkedPaths = $linkedPaths;
    }

    /**
     * @param Path $linkedPath
     */
    public function addLinkedPath(Path $linkedPath)
    {
        $this->linkedPaths[] = $linkedPath;
    }
}
