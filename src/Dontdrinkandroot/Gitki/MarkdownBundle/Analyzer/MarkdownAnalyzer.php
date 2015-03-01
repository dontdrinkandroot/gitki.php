<?php

namespace Dontdrinkandroot\Gitki\MarkdownBundle\Analyzer;

use Dontdrinkandroot\Gitki\BaseBundle\Analyzer\AnalyzedFile;
use Dontdrinkandroot\Gitki\BaseBundle\Analyzer\AnalyzerInterface;
use Dontdrinkandroot\Gitki\BaseBundle\Repository\GitRepositoryInterface;
use Dontdrinkandroot\Gitki\MarkdownBundle\Service\MarkdownService;
use Dontdrinkandroot\Path\FilePath;

class MarkdownAnalyzer implements AnalyzerInterface
{

    /**
     * @var MarkdownService
     */
    private $markdownService;

    /**
     * @var GitRepositoryInterface
     */
    private $gitRepository;

    public function __construct(GitRepositoryInterface $gitRepository, MarkdownService $markdownService)
    {
        $this->markdownService = $markdownService;
        $this->gitRepository = $gitRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedExtensions()
    {
        return ['md'];
    }

    /**
     * @param FilePath $path
     *
     * @return AnalyzedFile
     */
    public function analyze(FilePath $path)
    {
        $fileContent = $this->gitRepository->getContent($path);
        $parsedMarkdownDocument = $this->markdownService->parse($path, $fileContent);
        $analyzedFile = new AnalyzedFile();
        $analyzedFile->setContent($fileContent);
        $analyzedFile->setTitle($parsedMarkdownDocument->getTitle());
        $analyzedFile->setLinkedPaths($parsedMarkdownDocument->getLinkedPaths());

        return $analyzedFile;
    }
}
