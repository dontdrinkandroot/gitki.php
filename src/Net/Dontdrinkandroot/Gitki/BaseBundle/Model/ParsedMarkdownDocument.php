<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Model;


class ParsedMarkdownDocument
{

    private $source;

    private $html;

    private $title;

    private $pageLinks;

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

    /**
     * @param mixed $pageLinks
     */
    public function setPageLinks($pageLinks)
    {
        $this->pageLinks = $pageLinks;
    }

    /**
     * @return mixed
     */
    public function getPageLinks()
    {
        return $this->pageLinks;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $toc
     */
    public function setToc($toc)
    {
        $this->toc = $toc;
    }

    /**
     * @return mixed
     */
    public function getToc()
    {
        return $this->toc;
    }


} 