<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Tests\Model;


use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\Path\DirectoryPath;

class DirectoryPathTest extends \PHPUnit_Framework_TestCase
{

    public function testInvalid()
    {
        try {
            $path = DirectoryPath::parse('');
            $this->fail('Exception expected');
        } catch (\Exception $e) {
            /* Expected */
        }

        try {
            $path = DirectoryPath::parse('bla');
            $this->fail('Exception expected');
        } catch (\Exception $e) {
            /* Expected */
        }

        try {
            $path = DirectoryPath::parse('/../');
            $this->fail('Exception expected');
        } catch (\Exception $e) {
            /* Expected */
        }
    }

    public function testRoot()
    {
        $path = DirectoryPath::parse('/');
        $this->assertRootLevel($path);

        $path = new DirectoryPath();
        $this->assertRootLevel($path);
    }

    protected function assertRootLevel(DirectoryPath $path)
    {
        $this->assertFalse($path->hasParentPath());
        $this->assertEquals('/', $path->toUrlString());
        $this->assertNull($path->getName());
    }

    public function testFirstLevel()
    {
        $path = DirectoryPath::parse('/sub/');
        $this->assertFirstLevel($path);

        $path = DirectoryPath::parse('/sub//');
        $this->assertFirstLevel($path);

        $path = new DirectoryPath('sub');
        $this->assertFirstLevel($path);
    }

    protected function assertFirstLevel(DirectoryPath $path)
    {
        $this->assertEquals('sub', $path->getName());
        $this->assertEquals('/sub/', $path->toUrlString());
        $this->assertTrue($path->hasParentPath());

        $this->assertRootLevel($path->getParentPath());
    }

    public function testSecondLevel()
    {
        $path = DirectoryPath::parse('/sub/subsub/');
        $this->assertSecondLevel($path);

        $path = DirectoryPath::parse('/sub/subsub//');
        $this->assertSecondLevel($path);
    }

    protected function assertSecondLevel(DirectoryPath $path)
    {
        $this->assertEquals('subsub', $path->getName());
        $this->assertEquals('/sub/subsub/', $path->toUrlString());
        $this->assertTrue($path->hasParentPath());
        $this->assertFirstLevel($path->getParentPath());
    }


    public function testAppend()
    {
        $path = new DirectoryPath();
        $this->assertFalse($path->hasParentPath());
        $newPath = $path->appendDirectory('sub');
        $this->assertFirstLevel($newPath);

        $newPath = $newPath->appendDirectory('subsub');
        $this->assertSecondLevel($newPath);

        $filePath = $newPath->appendFile('index.md');
        $this->assertEquals('index.md', $filePath->getName());
        $this->assertEquals('index', $filePath->getFileName());
        $this->assertEquals('md', $filePath->getExtension());
    }

    public function testCollectPaths()
    {
        $path = DirectoryPath::parse("/sub/subsub/");
        $paths = $path->collectPaths();
        $this->assertCount(3, $paths);
        $this->assertEquals(null, $paths[0]->getName());
        $this->assertEquals('sub', $paths[1]->getName());
        $this->assertEquals('subsub', $paths[2]->getName());
    }

    public function testToStrings()
    {
        $path = DirectoryPath::parse("/sub/subsub/");
        $this->assertEquals('/sub/subsub/', $path->toUrlString());
        $this->assertEquals(
            DIRECTORY_SEPARATOR . 'sub' . DIRECTORY_SEPARATOR . 'subsub' . DIRECTORY_SEPARATOR,
            $path->toFileString()
        );
    }

    public function testComplicatedPath()
    {
        $path = DirectoryPath::parse('/sub/./bla//../subsub/');
        $this->assertSecondLevel($path);
    }

} 