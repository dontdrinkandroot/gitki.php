<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Service;


use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\ParsedMarkdownDocument;
use Net\Dontdrinkandroot\Utils\Path\FilePath;

interface MarkdownService
{

    /**
     * @param FilePath $path
     * @param string $content
     * @return ParsedMarkdownDocument
     */
    public function parse(FilePath $path, $content);

} 