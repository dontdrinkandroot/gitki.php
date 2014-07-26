<?php
namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Parser;

use Michelf\MarkdownExtra;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\ParsedMarkdownDocument;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\Path\DirectoryPath;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\Path\FilePath;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Repository\GitRepository;
use Net\Dontdrinkandroot\Symfony\ExtensionBundle\Utils\StringUtils;

class Attributes
{
    public $id;
    public $classes;

    public function toHtml()
    {
        $attr_str = "";
        if (!empty($this->id)) {
            $attr_str .= ' id="' . $this->id . '"';
        }
        if (!empty($this->classes)) {
            $attr_str .= ' class="' . implode(" ", $this->classes) . '"';
        }

        return $attr_str;
    }
}

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

class Heading
{
    public $level;
    public $text;
    public $parent;
    public $children = array();

    /**
     * @var Attributes
     */
    public $attributes;

    public function toHtml()
    {
        return '<h' . $this->level . $this->attributes->toHtml() . '>' . $this->text . '</h' . $this->level . '>';
    }
}

class RepositoryAwareMarkdownParser extends MarkdownExtra implements MarkdownParser
{

    /**
     * @var FilePath|null
     */
    private $path = null;

    protected $title;

    protected $toc = array();

    /**
     * @var Heading|null
     */
    protected $currentHeading = null;

    protected $headingIdx = 0;

    /**
     * @var GitRepository
     */
    private $gitRepository;

    public function __construct(FilePath $path, GitRepository $gitRepository)
    {
        parent::__construct();
        $this->gitRepository = $gitRepository;
        $this->path = $path;
    }

    public function parse($content)
    {
        $parsed = new ParsedMarkdownDocument();
        $parsed->setSource($content);
        $parsed->setHtml($this->transform($content));
        $parsed->setTitle($this->title);
        $parsed->setToc($this->toc);

        return $parsed;
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

    protected function _doHeaders_callback_setext($matches)
    {
        if ($matches[3] == '-' && preg_match('{^- }', $matches[1])) {
            return $matches[0];
        }

        $heading = new Heading();

        $heading->level = $matches[3]{0} == '=' ? 1 : 2;
        $heading->attributes = $this->doExtraAttributesAsObject('h' . $heading->level, $dummy =& $matches[2]);
        $heading->text = $this->runSpanGamut($matches[1]);

        return $this->headingToHtml($heading);
    }

    protected function _doHeaders_callback_atx($matches)
    {
        $heading = new Heading();

        $heading->level = strlen($matches[1]);
        $heading->attributes = $this->doExtraAttributesAsObject('h' . $heading->level, $dummy =& $matches[3]);
        $heading->text = $this->runSpanGamut($matches[2]);

        return $this->headingToHtml($heading);
    }

    private function headingToHtml(Heading $heading)
    {
        if (null === $this->title && $heading->level == 1) {
            $this->title = $heading->text;
        }

        if (empty($heading->attributes->id)) {
            $heading->attributes->id = 'heading' . $this->headingIdx;
        }
        $this->headingIdx++;

        if ($heading->level > 1) {
            if ($heading->level == 2) {
                $this->currentHeading = $heading;
                $this->toc[] = $heading;
            } else {
                if ($heading->level == 3) {
                    if ($this->currentHeading !== null && $this->currentHeading->level == 2) {
                        $this->currentHeading->children[] = $heading;
                        $heading->parent = $this->currentHeading;
                    }
                }
            }
        }


//        var_dump($this->toc);

        return "\n" . $this->hashBlock($heading->toHtml()) . "\n\n";
    }

    protected function doExtraAttributesAsObject($tag_name, $attr)
    {
        $attributes = new Attributes();

        if (empty($attr)) {
            return $attributes;
        }

        # Split on components
        preg_match_all('/[#.][-_:a-zA-Z0-9]+/', $attr, $matches);
        $elements = $matches[0];

        # handle classes and ids (only first id taken into account)
        $classes = array();
        $id = false;
        foreach ($elements as $element) {
            if ($element{0} == '.') {
                $attributes->classes[] = substr($element, 1);
            } else {
                if ($element{0} == '#') {
                    if ($id === false) {
                        $id = substr($element, 1);
                        $attributes->id = $id;
                    }
                }
            }
        }

        return $attributes;
    }


    private function targetExists($url)
    {
        try {
            $urlParts = parse_url($url);

            if (array_key_exists('scheme', $urlParts)) {
                return true;
            }

            if (array_key_exists('host', $urlParts)) {
                return true;
            }

            $urlPath = $urlParts['path'];
            $path = null;
            if (StringUtils::startsWith($urlPath, '/')) {
                if (StringUtils::endsWith($urlPath, '/')) {
                    $path = DirectoryPath::parse($urlPath);
                } else {
                    $path = FilePath::parse($urlPath);
                }
            } else {
                $path = $this->path->getParentPath()->appendPathString($urlPath);
            }

            $fileExists = $this->gitRepository->exists($path);

            return $fileExists;

        } catch (\Exception $e) {
        }

        return true;
    }


}