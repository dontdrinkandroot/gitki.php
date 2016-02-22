<?php


namespace Dontdrinkandroot\Gitki\WebBundle\Tests\Integration;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Dontdrinkandroot\GitkiBundle\Tests\GitRepositoryTestTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;

abstract class BaseIntegrationTest extends WebTestCase
{

    use GitRepositoryTestTrait;

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
     * {@inheritdoc}
     */
    protected function getRepositoryDataPath()
    {
        return realPath(__DIR__ . '/../../../../vendor/dontdrinkandroot/gitki-bundle/Tests/Data/repo/');
    }

    /**
     * {@inheritdoc}
     */
    protected function getRepositoryTargetPath()
    {
        return self::GIT_REPOSITORY_PATH;
    }

    /**
     * @return string[]
     */
    abstract protected function getFixtureClasses();
}
