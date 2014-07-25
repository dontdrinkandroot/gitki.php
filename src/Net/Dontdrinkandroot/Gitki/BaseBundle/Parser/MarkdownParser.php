<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Parser;


use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\ParsedMarkdownDocument;

interface MarkdownParser
{

    /**
     * @param string $content
     * @return ParsedMarkdownDocument
     */
    public function parse($content);

} 