<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Tests\Model;


use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\FilePath;

class FilePathTest extends \PHPUnit_Framework_TestCase
{

    public function testMarkdownPath()
    {
        $path = new FilePath('sub/index.md');
        $this->assertEquals('index.md', $path->getName());
    }

} 