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

} 