<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Service;


use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\Path\FilePath;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Parser\RepositoryAwareMarkdownParser;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Repository\GitRepository;

class RepositoryAwareMarkdownService implements MarkdownService
{

    protected $repository;

    public function __construct(GitRepository $repository)
    {
        $this->repository = $repository;
    }

    public function parse(FilePath $path, $content)
    {
        $parser = new RepositoryAwareMarkdownParser($path, $this->repository);

        return $parser->parse($content);
    }

} 