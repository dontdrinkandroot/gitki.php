<?php

namespace App\Tests\Integration;

use Dontdrinkandroot\GitkiBundle\Tests\GitRepositoryTestTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;

abstract class BaseIntegrationTest extends WebTestCase
{
    use GitRepositoryTestTrait;

    const GIT_REPOSITORY_PATH = '/tmp/gitkirepo/';

    protected function setUp()
    {
        $this->setUpRepo();
    }

    public function tearDown(): void
    {
        $this->tearDownRepo();
    }

    // TODO: Remove, replaces Liip Make Client until fixed for Symfony 4.3
    protected function makeBrowser($authentication = false, array $params = []): AbstractBrowser
    {
        return static::createClient(['environment' => $this->environment], $params);
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
