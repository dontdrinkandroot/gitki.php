<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Tests;

use GitWrapper\GitWrapper;
use Symfony\Component\Filesystem\Filesystem;

class GitRepositoryTestCase extends \PHPUnit_Framework_TestCase
{

    const TEST_PATH = '/tmp/gittest/';

    public function setUp()
    {
        $fileSystem = new Filesystem();
        $fileSystem->remove(self::TEST_PATH);

        $fileSystem->mkdir(self::TEST_PATH);
        $git = new GitWrapper();
        $git->init(self::TEST_PATH);
    }

    public function tearDown()
    {
        $fileSystem = new Filesystem();
        $fileSystem->remove(self::TEST_PATH);
    }
}
