<?php


namespace Dontdrinkandroot\Gitki\WebBundle\Tests\Integration;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use GitWrapper\GitWrapper;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class BaseIntegrationTest extends WebTestCase
{

    const GIT_REPOSITORY_PATH = '/tmp/gitkirepo/';

    /**
     * @var ReferenceRepository
     */
    protected $referenceRepository;

    protected function setUp()
    {
        /** @var ORMExecutor $executor */
        $executor = $this->loadFixtures($this->getFixtureClasses());
        $this->referenceRepository = $executor->getReferenceRepository();
        $this->setUpRepo();
    }

    public function tearDown()
    {
        $this->tearDownRepo();
    }

    /**
     * @param string $name
     *
     * @return object
     */
    protected function getReference($name)
    {
        return $this->referenceRepository->getReference($name);
    }

    /**
     * Init the git repository used for the tests.
     */
    protected function setUpRepo()
    {
        $testRepoPath = realPath(__DIR__ . '/../../../../vendor/dontdrinkandroot/gitki-bundle/Tests/Data/repo/');

        $fileSystem = new Filesystem();
        $fileSystem->remove(self::GIT_REPOSITORY_PATH);

        $fileSystem->mkdir(self::GIT_REPOSITORY_PATH);
        $fileSystem->mirror($testRepoPath, self::GIT_REPOSITORY_PATH);

        $git = new GitWrapper();
        $workingCopy = $git->init(self::GIT_REPOSITORY_PATH);
        $workingCopy->add('', ['A' => '']);
        $workingCopy->commit('Initial commit');
    }

    /**
     * Tear down the git repository used for the tests.
     */
    protected function tearDownRepo()
    {
        $fileSystem = new Filesystem();
        $fileSystem->remove(self::GIT_REPOSITORY_PATH);
    }

    /**
     * @return string[]
     */
    abstract protected function getFixtureClasses();
}
