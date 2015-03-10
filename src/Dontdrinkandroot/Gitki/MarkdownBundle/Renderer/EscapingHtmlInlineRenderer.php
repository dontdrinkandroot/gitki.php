<?php

namespace Dontdrinkandroot\Gitki\MarkdownBundle\Renderer;

use League\CommonMark\HtmlRendererInterface;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Element\Html;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;

class EscapingHtmlInlineRenderer implements InlineRendererInterface
{
    /**
     * @param AbstractInline        $inline
     * @param HtmlRendererInterface $htmlRenderer
     *
     * @return string
     */
    public function render(AbstractInline $inline, HtmlRendererInterface $htmlRenderer)
    {
        if (!($inline instanceof Html)) {
            throw new \InvalidArgumentException('Incompatible inline type: ' . get_class($inline));
        }

        return htmlspecialchars($inline->getContent(), ENT_HTML5);
    }
}
