<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Model;

use Dontdrinkandroot\Path\FilePath;

class SearchResult
{

    /**
     * @var FilePath
     */
    private $path;

    /**
     * @var string
     */
    private $title;

    /**
     * @var float
     */
    private $score;

    /**
     * @param FilePath $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return FilePath
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param float $score
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

    /**
     * @return float
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}
