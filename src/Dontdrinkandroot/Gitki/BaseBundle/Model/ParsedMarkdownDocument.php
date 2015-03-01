<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Model;

use Dontdrinkandroot\Path\Path;

class ParsedMarkdownDocument
{

    private $source;

    private $html;

    private $title;

    /**
     * @var Path[]
     */
    private $linkedPaths;

    private $toc;

    public function setSource($source)
    {
        $this->source = $source;
    }

    public function setHtml($html)
    {
        $this->html = $html;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getHtml()
    {
        return $this->html;
    }

    public function setLinkedPaths($pageLinks)
    {
        $this->linkedPaths = $pageLinks;
    }

    /**
     * @return Path[]
     */
    public function getLinkedPaths()
    {
        return $this->linkedPaths;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setToc($toc)
    {
        $this->toc = $toc;
    }

    public function getToc()
    {
        return $this->toc;
    }
}
