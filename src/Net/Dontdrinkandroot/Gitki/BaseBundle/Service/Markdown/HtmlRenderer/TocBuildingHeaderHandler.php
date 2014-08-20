<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Service\Markdown\HtmlRenderer;


use Net\Dontdrinkandroot\ObjectiveMarkdown\Model\Element\Block\Header;
use Net\Dontdrinkandroot\ObjectiveMarkdown\Renderer\Html\Handler\HeaderHandler;
use Net\Dontdrinkandroot\ObjectiveMarkdown\Renderer\Html\HtmlRenderContext;

class TocBuildingHeaderHandler extends HeaderHandler
{

    private $toc = array();

    private $title = null;

    private $count = 0;

    private $current = array();

    /**
     * @param Header            $element
     * @param HtmlRenderContext $context
     *
     * @return string
     */
    public function handle($element, HtmlRenderContext $context)
    {
        $level = $element->getLevel();
        $text = '';
        $id = 'heading' . $this->count;
        foreach ($element->getChildren() as $child) {
            $class = get_class($child);
            $handler = $context->getHandler($class);
            $text .= $handler->handle($child, $context);
        }

        if (null === $this->title && $level == 1) {
            $this->title = $text;
        } else {
            if ($level >= 2) {
                for ($i = $level; $i <= 6; $i++) {
                    unset($this->current[$i]);
                }
                $this->current[$level] = [
                    'id' => $id,
                    'text' => $text,
                    'level' => $level,
                    'children' => []
                ];
                if ($level == 2) {
                    $this->toc[] = & $this->current[$level];
                } else {
                    if (isset($this->current[$level - 1])) {
                        $this->current[$level - 1]['children'][] = & $this->current[$level];
                    }
                }

            }
        }

        $tagName = 'h' . $level;
        $markup = $this->renderOpeningTag($tagName, ['id' => $id]);
        $markup .= $text;
        $markup .= $this->renderClosingTag($tagName);

        $this->count++;

        return $markup;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getToc()
    {
        return $this->toc;
    }

} 