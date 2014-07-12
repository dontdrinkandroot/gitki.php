<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Tests\Model;


use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\Path;

class PathTest extends \PHPUnit_Framework_TestCase
{

    public function testRoot()
    {
        $path = new Path('');
        $this->assertCount(0, $path->getSegments());

        $path = new Path('/');
        $this->assertCount(0, $path->getSegments());

        $path = new Path('//');
        $this->assertCount(0, $path->getSegments());
    }

    public function testFirstLevel()
    {
        $path = new Path('sub');
        $this->assertCount(1, $path->getSegments());
        $this->assertEquals('sub', $path->getSegment(0)->toString());

        $path = new Path('sub/');
        $this->assertCount(1, $path->getSegments());
        $this->assertEquals('sub', $path->getSegment(0)->toString());

        $path = new Path('/sub');
        $this->assertCount(1, $path->getSegments());
        $this->assertEquals('sub', $path->getSegment(0)->toString());

        $path = new Path('/sub/');
        $this->assertCount(1, $path->getSegments());
        $this->assertEquals('sub', $path->getSegment(0)->toString());

        $path = new Path('//sub/');
        $this->assertCount(1, $path->getSegments());
        $this->assertEquals('sub', $path->getSegment(0)->toString());

        $path = new Path('/sub//');
        $this->assertCount(1, $path->getSegments());
        $this->assertEquals('sub', $path->getSegment(0)->toString());
    }

    public function testSecondLevel()
    {
        $path = new Path('sub/subsub');
        $this->assertCount(2, $path->getSegments());
        $this->assertEquals('sub', $path->getSegment(0)->getName());
        $this->assertEquals('subsub', $path->getSegment(1)->getName());
        $this->assertEquals('sub/subsub', $path->getSegment(1)->toString());

        $path = new Path('/sub/subsub');
        $this->assertCount(2, $path->getSegments());
        $this->assertEquals('sub', $path->getSegment(0)->getName());
        $this->assertEquals('subsub', $path->getSegment(1)->getName());
        $this->assertEquals('sub/subsub', $path->getSegment(1)->toString());

        $path = new Path('sub/subsub/');
        $this->assertCount(2, $path->getSegments());
        $this->assertEquals('sub', $path->getSegment(0)->getName());
        $this->assertEquals('subsub', $path->getSegment(1)->getName());
        $this->assertEquals('sub/subsub', $path->getSegment(1)->toString());

        $path = new Path('/sub/subsub/');
        $this->assertCount(2, $path->getSegments());
        $this->assertEquals('sub', $path->getSegment(0)->getName());
        $this->assertEquals('subsub', $path->getSegment(1)->getName());
        $this->assertEquals('sub/subsub', $path->getSegment(1)->toString());

        $path = new Path('//sub/subsub/');
        $this->assertCount(2, $path->getSegments());
        $this->assertEquals('sub', $path->getSegment(0)->getName());
        $this->assertEquals('subsub', $path->getSegment(1)->getName());
        $this->assertEquals('sub/subsub', $path->getSegment(1)->toString());

        $path = new Path('/sub/subsub//');
        $this->assertCount(2, $path->getSegments());
        $this->assertEquals('sub', $path->getSegment(0)->getName());
        $this->assertEquals('subsub', $path->getSegment(1)->getName());
        $this->assertEquals('sub/subsub', $path->getSegment(1)->toString());
    }

    public function testMarkdownPath()
    {
        $path = new Path('sub/index.md');
        $this->assertEquals('index.md', $path->getName());
    }

    public function testGetParentPath()
    {
        $path = new Path('');
        $this->assertNull($path->getParentPath());

        $path = new Path('sub');
        $parentPath = $path->getParentPath();
        $this->assertEquals(0, $parentPath->getNumSegments());
        $this->assertEquals(1, $path->getNumSegments());

        $path = new Path('sub/subsub');
        $parentPath = $path->getParentPath();
        $this->assertEquals(1, $parentPath->getNumSegments());
        $this->assertEquals(2, $path->getNumSegments());
        $this->assertEquals('sub', $parentPath->getName());
    }

    public function testAddSegment()
    {
        $path = new Path();
        $newPath = $path->addSegment('sub');
        $this->assertNull($path->getLastSegment());
        $this->assertEquals('sub', $newPath->getLastSegment()->toString());

        $newPath = $newPath->addSegment('subsub');
        $this->assertEquals('sub/subsub', $newPath->getLastSegment()->toString());
        $this->assertEquals(2, $newPath->getNumSegments());
    }

} 