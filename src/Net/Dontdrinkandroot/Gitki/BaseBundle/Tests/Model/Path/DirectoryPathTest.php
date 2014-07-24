<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Tests\Model;


use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\Path\DirectoryPath;

class DirectoryPathTest extends \PHPUnit_Framework_TestCase
{

    public function testInvalid()
    {
        try {
            $path = DirectoryPath::parse('bla');
            $this->fail('Exception expected');
        } catch (\Exception $e) {
            /* Expected */
        }

        try {
            $path = new DirectoryPath('bla/bla');
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

        try {
            $path = new DirectoryPath();
            $path->appendDirectory('');
            $this->fail('Exception expected');
        } catch (\Exception $e) {
            /* Expected */
        }

        try {
            $path = new DirectoryPath();
            $path->appendFile('');
            $this->fail('Exception expected');
        } catch (\Exception $e) {
            /* Expected */
        }

        try {
            $path = new DirectoryPath();
            $path->appendDirectory('bla/bla');
            $this->fail('Exception expected');
        } catch (\Exception $e) {
            /* Expected */
        }

        try {
            $path = new DirectoryPath();
            $path->appendFile('bla/bla');
            $this->fail('Exception expected');
        } catch (\Exception $e) {
            /* Expected */
        }
    }

    public function testRoot()
    {
        $path = DirectoryPath::parse('/');
        $this->assertRootLevel($path);

        $path = DirectoryPath::parse('');
        $this->assertRootLevel($path);

        $path = new DirectoryPath();
        $this->assertRootLevel($path);
    }

    protected function assertRootLevel(DirectoryPath $path)
    {
        $this->assertFalse($path->hasParentPath());
        $this->assertEquals('/', $path->toAbsoluteUrlString());
        $this->assertEquals(DIRECTORY_SEPARATOR, $path->toAbsoluteFileString());
        $this->assertNull($path->getName());
        $this->assertTrue($path->isRoot());
    }

    public function testFirstLevel()
    {
        $path = DirectoryPath::parse('/sub/');
        $this->assertFirstLevel($path);

        $path = DirectoryPath::parse('sub/');
        $this->assertFirstLevel($path);

        $path = DirectoryPath::parse('/sub//');
        $this->assertFirstLevel($path);

        $path = new DirectoryPath('sub');
        $this->assertFirstLevel($path);
    }

    protected function assertFirstLevel(DirectoryPath $path)
    {
        $this->assertEquals('sub', $path->getName());
        $this->assertEquals('/sub/', $path->toAbsoluteUrlString());
        $this->assertTrue($path->hasParentPath());
        $this->assertFalse($path->isRoot());

        $this->assertRootLevel($path->getParentPath());
    }

    public function testSecondLevel()
    {
        $path = DirectoryPath::parse('/sub/subsub/');
        $this->assertSecondLevel($path);

        $path = DirectoryPath::parse('/sub/subsub//');
        $this->assertSecondLevel($path);

        $path = DirectoryPath::parse('sub/subsub//');
        $this->assertSecondLevel($path);
    }

    protected function assertSecondLevel(DirectoryPath $path)
    {
        $this->assertEquals('subsub', $path->getName());
        $this->assertEquals('/sub/subsub/', $path->toAbsoluteUrlString());
        $this->assertTrue($path->hasParentPath());
        $this->assertFirstLevel($path->getParentPath());
        $this->assertFalse($path->isRoot());
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
        $this->assertEquals('/sub/subsub/', $path->toAbsoluteUrlString());
        $this->assertEquals(
            DIRECTORY_SEPARATOR . 'sub' . DIRECTORY_SEPARATOR . 'subsub' . DIRECTORY_SEPARATOR,
            $path->toAbsoluteFileString()
        );

        $this->assertEquals('sub/subsub/', $path->toRelativeUrlString());
        $this->assertEquals(
            'sub' . DIRECTORY_SEPARATOR . 'subsub' . DIRECTORY_SEPARATOR,
            $path->toRelativeFileString()
        );
    }

    public function testComplicatedPath()
    {
        $path = DirectoryPath::parse('/sub/./bla//../subsub/');
        $this->assertSecondLevel($path);
    }

    public function testPrepend()
    {
        $path1 = DirectoryPath::parse("/sub/");
        $path2 = DirectoryPath::parse("/subsub/");
        $mergedPath = $path2->prepend($path1);
        $this->assertSecondLevel($mergedPath);
    }

    public function testAppendPathString()
    {
        $path = DirectoryPath::parse('/sub/');

        $directoryPath = $path->appendPathString('subsub/');
        $this->assertSecondLevel($directoryPath);

        $filePath = $path->appendPathString('subsub/index.md');
        $this->assertSecondLevel($filePath->getParentPath());
        $this->assertEquals('index.md', $filePath->getName());
        $this->assertEquals('index', $filePath->getFileName());
        $this->assertEquals('md', $filePath->getExtension());

        $path = DirectoryPath::parse('/sub/bla/');
        $filePath = $path->appendPathString('./../subsub/index.md');
        $this->assertSecondLevel($filePath->getParentPath());
        $this->assertEquals('index.md', $filePath->getName());
        $this->assertEquals('index', $filePath->getFileName());
        $this->assertEquals('md', $filePath->getExtension());
    }

} 