<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Service\Markdown;

use Dontdrinkandroot\Gitki\BaseBundle\Model\ParsedMarkdownDocument;
use Dontdrinkandroot\Path\FilePath;

interface MarkdownService
{

    /**
     * @param FilePath $path
     * @param string   $content
     *
     * @return ParsedMarkdownDocument
     */
    public function parse(FilePath $path, $content);
}
