<?php

namespace App\Tests\Integration;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Dontdrinkandroot\GitkiBundle\Tests\GitRepositoryTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class BaseIntegrationTest extends WebTestCase
{
    use GitRepositoryTestTrait;
    use FixturesTrait;

    const GIT_REPOSITORY_PATH = '/tmp/gitkirepo/';

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->setUpRepo();
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(): void
    {
        $this->tearDownRepo();
    }

    protected function loadKernelAndFixtures(array $classNames = []): ReferenceRepository
    {
        self::bootKernel();
        return $this->loadFixtures($classNames)->getReferenceRepository();
    }

    /**
     * {@inheritdoc}
     */
    protected function getRepositoryTemplatePath()
    {
        return realpath(__DIR__ . '/../../vendor/dontdrinkandroot/gitki-bundle/Tests/Data/repo/');
    }

    /**
     * {@inheritdoc}
     */
    protected function getRepositoryTargetPath()
    {
        return self::GIT_REPOSITORY_PATH;
    }
}
