<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Tests\Model;


use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\PathSegment;

class PathSegmentTest extends \PHPUnit_Framework_TestCase
{


    public function testBasic()
    {
        $pathSegment1 = new PathSegment('sub');
        $this->assertNull($pathSegment1->getParentSegment());
        $this->assertEquals('sub', $pathSegment1->getName());
        $this->assertEquals('sub', $pathSegment1->toString());

        $pathSegment2 = new PathSegment('subsub', $pathSegment1);
        $this->assertEquals($pathSegment1, $pathSegment2->getParentSegment());
        $this->assertEquals('subsub', $pathSegment2->getName());
        $this->assertEquals('sub/subsub', $pathSegment2->toString());
    }
} 