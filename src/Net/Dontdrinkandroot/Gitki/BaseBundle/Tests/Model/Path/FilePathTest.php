<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Tests\Model;


use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\Path\DirectoryPath;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\Path\FilePath;

class FilePathTest extends \PHPUnit_Framework_TestCase
{

    public function testBasic()
    {
        $path = FilePath::parse('/index.md');
        $this->assertEquals('index.md', $path->getName());
        $this->assertEquals('md', $path->getExtension());
        $this->assertEquals('index', $path->getFileName());

        $path = FilePath::parse('/sub/sub/index.md');
        $this->assertEquals('index.md', $path->getName());
        $this->assertEquals('md', $path->getExtension());
        $this->assertEquals('index', $path->getFileName());

        $this->assertEquals('/sub/sub/', $path->getParentPath()->toAbsoluteUrlString());
    }

    public function testNoExtension()
    {
        $path = FilePath::parse('/index');
        $this->assertEquals('index', $path->getName());
        $this->assertNull($path->getExtension());
        $this->assertEquals('index', $path->getFileName());

        $path = FilePath::parse('/sub/index');
        $this->assertEquals('index', $path->getName());
        $this->assertNull($path->getExtension());
        $this->assertEquals('index', $path->getFileName());

        $this->assertEquals('/sub/', $path->getParentPath()->toAbsoluteUrlString());
    }

    public function testDotFile()
    {
        $path = FilePath::parse('/.index');
        $this->assertEquals('.index', $path->getName());
        $this->assertNull($path->getExtension());
        $this->assertEquals('.index', $path->getFileName());

        $path = FilePath::parse('/sub/.index');
        $this->assertEquals('.index', $path->getName());
        $this->assertNull($path->getExtension());
        $this->assertEquals('.index', $path->getFileName());

        $this->assertEquals('/sub/', $path->getParentPath()->toAbsoluteUrlString());
    }

    public function testInvalidPath()
    {
        try {
            $path = new FilePath(null);
            $this->fail('Exception expected');
        } catch (\Exception $e) {
            /* Expected */
        }

        try {
            $path = FilePath::parse('');
            $this->fail('Exception expected');
        } catch (\Exception $e) {
            /* Expected */
        }

        try {
            $path = FilePath::parse('/');
            $this->fail('Exception expected');
        } catch (\Exception $e) {
            /* Expected */
        }

        try {
            $path = FilePath::parse('bla/');
            $this->fail('Exception expected');
        } catch (\Exception $e) {
            /* Expected */
        }
    }

    public function testCollectPaths()
    {
        $path = FilePath::parse("/sub/subsub/index.md");
        $paths = $path->collectPaths();
        $this->assertCount(4, $paths);
        $this->assertEquals(null, $paths[0]->getName());
        $this->assertEquals('sub', $paths[1]->getName());
        $this->assertEquals('subsub', $paths[2]->getName());
        $this->assertEquals('index', $paths[3]->getFilename());
    }

    public function testToStrings()
    {
        $path = FilePath::parse("/sub/subsub/index.md");
        $this->assertEquals('/sub/subsub/index.md', $path->toAbsoluteUrlString());
        $this->assertEquals(
            DIRECTORY_SEPARATOR . 'sub' . DIRECTORY_SEPARATOR . 'subsub' . DIRECTORY_SEPARATOR . 'index.md',
            $path->toAbsoluteFileString()
        );
    }

    public function testPrepend()
    {
        $path1 = DirectoryPath::parse("/sub/");
        $path2 = FilePath::parse("/subsub/index.md");
        $mergedPath = $path2->prepend($path1);
        $this->assertEquals('/sub/subsub/index.md', $mergedPath->toAbsoluteUrlString());
    }

}