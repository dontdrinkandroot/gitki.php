<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Tests\Model;


use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\Path\DirectoryPath;

class DirectoryPathTest extends \PHPUnit_Framework_TestCase
{

    public function testInvalid()
    {
        try {
            $path = DirectoryPath::parse('/');
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
    }

    public function testRoot()
    {
        $path = DirectoryPath::parse('');
        $this->assertFalse($path->hasParentPath());
        $this->assertEquals('', $path->toString());
    }

    public function testFirstLevel()
    {
        $path = DirectoryPath::parse('sub/');
        $this->assertFirstLevel($path);

        $path = DirectoryPath::parse('sub//');
        $this->assertFirstLevel($path);
    }

    protected function assertFirstLevel(DirectoryPath $path)
    {
        $this->assertEquals('sub', $path->getName());
        $this->assertEquals('sub/', $path->toString());
        $this->assertTrue($path->hasParentPath());
        $this->assertNull($path->getParentPath()->getName());
    }

    public function testSecondLevel()
    {
        $path = DirectoryPath::parse('sub/subsub/');
        $this->assertSecondLevel($path);

        $path = DirectoryPath::parse('sub/subsub//');
        $this->assertSecondLevel($path);
    }

    protected function assertSecondLevel(DirectoryPath $path)
    {
        $this->assertEquals('subsub', $path->getName());
        $this->assertEquals('sub/subsub/', $path->toString());
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

} 