<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Service;


use Net\Dontdrinkandroot\ObjectiveMarkdown\Model\Block\Header;
use Net\Dontdrinkandroot\ObjectiveMarkdown\Renderer\Html\Handler\HeaderHandler;
use Net\Dontdrinkandroot\ObjectiveMarkdown\Renderer\Html\HtmlRenderContext;

class TocBuildingHeaderHandler extends HeaderHandler
{

    private $toc = array();

    private $title = null;

    private $count = 0;

    private $currentH2 = null;

    /**
     * @param Header $element
     * @param HtmlRenderContext $context
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
        }

        if ($level == 2) {

            unset($this->currentH2);

            $this->currentH2 = array(
                'text' => $text,
                'id' => $id,
                'children' => array()
            );

            $this->toc[] = & $this->currentH2;
        }

        if ($level == 3) {
            if (null !== $this->currentH2) {
                $this->currentH2['children'][] = array(
                    'text' => $text,
                    'id' => $id,
                );
            }
        }

        $markup = '<h' . $level . ' id="' . $id . '">';
        $markup .= $text;
        $markup .= '</h' . $level . '>';

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