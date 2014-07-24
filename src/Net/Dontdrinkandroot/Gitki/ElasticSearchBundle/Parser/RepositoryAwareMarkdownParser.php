<?php


namespace Net\Dontdrinkandroot\Gitki\ElasticSearchBundle\Parser;


use Knp\Bundle\MarkdownBundle\Parser\MarkdownParser;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\Path\FilePath;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Repository\GitRepository;

class Link
{

    public $url;
    public $title = null;
    public $text;
    public $targetExists = true;

    public function toHtml()
    {
        $html = '<a href="' . $this->url . '"';
        if (null !== $this->title) {
            $html .= ' title="' . $this->title . '"';
        }
        if (!$this->targetExists) {
            $html .= ' class="missing"';
        }
        $html .= '>';
        $html .= $this->text;
        $html .= '</a>';

        return $html;
    }

}

class RepositoryAwareMarkdownParser extends MarkdownParser
{

    /**
     * @var FilePath|null
     */
    private $currentPath = null;

    private $gitRepository;

    public function __construct(GitRepository $gitRepository)
    {
        parent::__construct();
        $this->gitRepository = $gitRepository;
    }

    public function transformMarkdown($text)
    {
        if (func_num_args() > 1) {
            $this->currentPath = func_get_arg(1);
        } else {
            $this->currentPath = null;
        }

        return parent::transformMarkdown($text);
    }

    protected function _doAnchors_reference_callback($matches)
    {
        $whole_match = $matches[1];
        $link_text = $matches[2];
        $link_id =& $matches[3];

        if ($link_id == "") {
            # for shortcut links like [this][] or [this].
            $link_id = $link_text;
        }

        # lower-case and turn embedded newlines into spaces
        $link_id = strtolower($link_id);
        $link_id = preg_replace('{[ ]?\n}', ' ', $link_id);

        if (isset($this->urls[$link_id])) {

            $link = new Link();

            $url = $this->urls[$link_id];
            $link->url = $this->encodeAttribute($url);
            $link->targetExists = $this->targetExists($url);

            if (isset($this->titles[$link_id])) {
                $title = $this->titles[$link_id];
                $title = $this->encodeAttribute($title);
                $link->title = $title;
            }

            $link_text = $this->runSpanGamut($link_text);
            $link->text = $link_text;

            return $this->hashPart($link->toHtml());

        } else {
            return $whole_match;
        }
    }

    protected function _doAnchors_inline_callback($matches)
    {
        $whole_match = $matches[1];
        $link_text = $this->runSpanGamut($matches[2]);
        $url = $matches[3] == '' ? $matches[4] : $matches[3];
        $title =& $matches[7];

        $link = new Link();

        $url = $this->encodeAttribute($url);
        $link->url = $url;
        $link->targetExists = $this->targetExists($url);

        if (isset($title)) {
            $title = $this->encodeAttribute($title);
            $link->title = $title;
        }

        $link_text = $this->runSpanGamut($link_text);
        $link->text = $link_text;

        return $this->hashPart($link->toHtml());
    }

    private function targetExists($url)
    {
        if (null === $this->currentPath) {
            return true;
        }

        //TODO: Filter non internal urls (http:...)

        try {
            $repositoryPath = $this->currentPath->getParentPath()->appendPathString($url);
            $fileExists = $this->gitRepository->exists($repositoryPath);
            //var_dump($repositoryPath . ':' . $fileExists);
            if (!$fileExists) {
                return false;
            }
        } catch (\Exception $e) {
        }


        return true;
    }

}