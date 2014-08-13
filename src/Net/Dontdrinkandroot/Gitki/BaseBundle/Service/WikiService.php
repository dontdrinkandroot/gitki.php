<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Service;

use Net\Dontdrinkandroot\Gitki\BaseBundle\Event\MarkdownDocumentDeletedEvent;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Event\MarkdownDocumentSavedEvent;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Exception\DirectoryNotEmptyException;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Exception\FileExistsException;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Exception\PageLockedException;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Exception\PageLockExpiredException;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\DirectoryListing;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\ParsedMarkdownDocument;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Repository\GitRepository;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Security\User;
use Net\Dontdrinkandroot\Utils\Path\DirectoryPath;
use Net\Dontdrinkandroot\Utils\Path\FilePath;
use Net\Dontdrinkandroot\Utils\Path\Path;
use Net\Dontdrinkandroot\Utils\StringUtils;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class WikiService
{

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var GitRepository
     */
    protected $gitRepository;

    /**
     * @var MarkdownService
     */
    protected $markdownService;

    public function __construct(
        GitRepository $gitRepository,
        MarkdownService $markdownService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->gitRepository = $gitRepository;
        $this->markdownService = $markdownService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function exists(Path $relativePath)
    {
        return $this->gitRepository->exists($relativePath);
    }


    public function createLock(User $user, FilePath $relativeFilePath)
    {
        $relativeLockPath = $this->getLockPath($relativeFilePath);
        $relativeLockDir = $relativeLockPath->getParentPath();

        $this->assertUnlocked($user, $relativeLockPath);

        if (!$this->gitRepository->exists($relativeLockDir)) {
            $this->gitRepository->mkdir($relativeLockDir);
        }

        if ($this->gitRepository->exists($relativeLockPath)) {
            $this->gitRepository->touch($relativeLockPath);
        } else {
            $this->gitRepository->putContent($relativeLockPath, $user->getEmail());
        }
    }

    public function removeLock(User $user, FilePath $relativeFilePath)
    {
        $relativeLockPath = $this->getLockPath($relativeFilePath);
        if (!$this->gitRepository->exists($relativeLockPath)) {
            return;
        }

        if ($this->isLockExpired($relativeLockPath)) {
            return;
        }

        $lockLogin = $this->getLockLogin($relativeLockPath);
        if ($lockLogin != $user->getEmail()) {
            throw new \Exception('Cannot remove lock of different user');
        }

        $this->removeLockFile($relativeLockPath);
    }

    public function getContent(FilePath $relativeFilePath)
    {
        return $this->gitRepository->getContent($relativeFilePath);
    }

    /**
     * @param FilePath $relativeFilePath
     * @return ParsedMarkdownDocument
     */
    public function getParsedMarkdownDocument(FilePath $relativeFilePath)
    {
        $content = $this->getContent($relativeFilePath);

        return $this->markdownService->parse($relativeFilePath, $content);
    }

    public function savePage(User $user, FilePath $relativeFilePath, $content, $commitMessage)
    {
        if (empty($commitMessage)) {
            throw new \Exception('Commit message must not be empty');
        }

        $relativeLockPath = $this->getLockPath($relativeFilePath);
        $this->assertHasLock($user, $relativeLockPath);

        $this->gitRepository->putContent($relativeFilePath, $content);

        $this->gitRepository->addAndCommit($this->getAuthor($user), $commitMessage, $relativeFilePath);

        $parsedMarkdownDocument = $this->markdownService->parse($relativeFilePath, $content);

        $this->eventDispatcher->dispatch(
            'ddr.gitki.wiki.markdown_document.saved',
            new MarkdownDocumentSavedEvent($relativeFilePath, $user->getEmail(), time(
            ), $parsedMarkdownDocument, $commitMessage)
        );
    }

    public function holdLock(User $user, FilePath $relativeFilePath)
    {
        $lockPath = $this->getLockPath($relativeFilePath);
        $this->assertHasLock($user, $lockPath);

        $this->gitRepository->touch($lockPath);

        return $this->getLockExpiry($lockPath);
    }

    public function deleteFile(User $user, FilePath $relativeFilePath, $commitMessage)
    {
        if (empty($commitMessage)) {
            throw new \Exception('Commit message must not be empty');
        }

        $this->createLock($user, $relativeFilePath);

        $this->gitRepository->removeAndCommit($this->getAuthor($user), $commitMessage, $relativeFilePath);

        $this->removeLock($user, $relativeFilePath);

        if (StringUtils::endsWith($relativeFilePath->getName(), '.md')) {
            $this->eventDispatcher->dispatch(
                'ddr.gitki.wiki.markdown_document.deleted',
                new MarkdownDocumentDeletedEvent($relativeFilePath, $user->getEmail(), time(), $commitMessage)
            );
        }
    }

    public function deleteDirectory(DirectoryPath $relativeDirectoryPath)
    {
        $absoluteDirectoryPath = $this->gitRepository->getAbsolutePath($relativeDirectoryPath);
        $finder = new Finder();
        $finder->in($this->gitRepository->getAbsolutePathString($relativeDirectoryPath));
        $numFiles = $finder->files()->count();
        if ($numFiles > 0) {
            throw new DirectoryNotEmptyException($relativeDirectoryPath->toRelativeFileString() . ' is not empty');
        }

        $fileSystem = new Filesystem();
        $fileSystem->remove($absoluteDirectoryPath);
    }

    public function renameFile(User $user, FilePath $relativeOldFilePath, FilePath $relativeNewFilePath, $commitMessage)
    {
        if ($this->gitRepository->exists($relativeNewFilePath)) {
            throw new FileExistsException('File ' . $relativeNewFilePath->toRelativeFileString() . ' already exists');
        }

        if (empty($commitMessage)) {
            throw new \Exception('Commit message must not be empty');
        }

        $oldLockPath = $this->getLockPath($relativeOldFilePath);

        $this->assertHasLock($user, $oldLockPath);
        $this->createLock($user, $relativeNewFilePath);

        $this->gitRepository->moveAndCommit(
            $this->getAuthor($user),
            $commitMessage,
            $relativeOldFilePath,
            $relativeNewFilePath
        );

        $this->removeLock($user, $relativeOldFilePath);
        $this->removeLock($user, $relativeNewFilePath);

        if (StringUtils::endsWith($relativeOldFilePath->getName(), '.md')) {
            $this->eventDispatcher->dispatch(
                'ddr.gitki.wiki.markdown_document.deleted',
                new MarkdownDocumentDeletedEvent($relativeOldFilePath, $user->getName(), time(), $commitMessage)
            );
        }

        if (StringUtils::endsWith($relativeNewFilePath->getName(), '.md')) {
            $content = $this->getContent($relativeNewFilePath);
            $parsedMarkdownDocument = $this->markdownService->parse($relativeNewFilePath, $content);
            $this->eventDispatcher->dispatch(
                'ddr.gitki.wiki.markdown_document.saved',
                new MarkdownDocumentSavedEvent($relativeNewFilePath, $user->getEmail(), time(
                ), $parsedMarkdownDocument, $commitMessage)
            );
        }
    }

    public function addFile(User $user, FilePath $relativeFilePath, UploadedFile $uploadedFile, $commitMessage)
    {
        $relativeDirectoryPath = $relativeFilePath->getParentPath();

        if ($this->gitRepository->exists($relativeFilePath)) {
            throw new FileExistsException('File ' . $relativeFilePath->toRelativeFileString() . ' already exists');
        }

        if (!$this->gitRepository->exists($relativeDirectoryPath)) {
            $this->gitRepository->mkdir($relativeDirectoryPath);
        }

        $this->createLock($user, $relativeFilePath);
        $uploadedFile->move(
            $this->gitRepository->getAbsolutePath($relativeDirectoryPath),
            $relativeFilePath->getName()
        );

        $this->gitRepository->addAndCommit($this->getAuthor($user), $commitMessage, $relativeFilePath);

        $this->removeLock($user, $relativeFilePath);
    }

    protected function assertUnlocked(User $user, FilePath $relativeLockPath)
    {
        if (!$this->gitRepository->exists($relativeLockPath)) {
            return true;
        }

        if ($this->isLockExpired($relativeLockPath)) {
            return true;
        }

        $lockLogin = $this->getLockLogin($relativeLockPath);
        if ($lockLogin == $user->getEmail()) {
            return true;
        }

        throw new PageLockedException($lockLogin, $this->getLockExpiry($relativeLockPath));
    }

    /**
     * @return FilePath[]
     */
    public function findAllMarkdownFiles()
    {
        $finder = new Finder();
        $finder->in($this->gitRepository->getRepositoryPath()->toAbsoluteFileString());
        $finder->name('*.md');

        $filePaths = array();

        foreach ($finder->files() as $file) {
            /** @var SplFileInfo $file */
            $filePaths[] = FilePath::parse('/' . $file->getRelativePathname());
        }

        return $filePaths;
    }


    public function listDirectory(DirectoryPath $relativeDirectoryPath)
    {
        /* @var SplFileInfo[] $pages */
        $pages = array();
        /* @var SplFileInfo[] $subDirectories */
        $subDirectories = array();
        /* @var SplFileInfo[] $otherFiles */
        $otherFiles = array();

        $finder = new Finder();
        $finder->in($this->gitRepository->getAbsolutePathString($relativeDirectoryPath));
        $finder->depth(0);
        foreach ($finder->files() as $file) {
            /* @var \Symfony\Component\Finder\SplFileInfo $file */
            if ($file->getExtension() == "md") {
                $pages[] = $file;
            } else {
                if ($file->getExtension() != 'lock') {
                    $otherFiles[] = $file;
                }
            }
        }

        $finder = new Finder();
        $finder->in($this->gitRepository->getAbsolutePathString($relativeDirectoryPath));
        $finder->depth(0);
        $finder->ignoreDotFiles(true);
        foreach ($finder->directories() as $directory) {
            /* @var \Symfony\Component\Finder\SplFileInfo $directory */
            $subDirectories[] = $directory;
        }


        usort(
            $pages,
            function (SplFileInfo $a, SplFileInfo $b) {
                return strcmp($a->getFilename(), $b->getFilename());
            }
        );
        usort(
            $subDirectories,
            function (SplFileInfo $a, SplFileInfo $b) {
                return strcmp($a->getFilename(), $b->getFilename());
            }
        );
        usort(
            $otherFiles,
            function (SplFileInfo $a, SplFileInfo $b) {
                return strcmp($a->getFilename(), $b->getFilename());
            }
        );

        return new DirectoryListing($relativeDirectoryPath, $pages, $subDirectories, $otherFiles);
    }

    /**
     * @param FilePath $path
     * @return File
     */
    public function getFile(FilePath $path)
    {
        $absolutePath = $this->gitRepository->getAbsolutePath($path);

        return new File($absolutePath->toAbsoluteFileString());
    }

    public function getHistory($maxCount)
    {
        return $this->gitRepository->getWorkingCopyHistory($maxCount);
    }

    public function getFileHistory(FilePath $path, $maxCount = null)
    {
        return $this->gitRepository->getFileHistory($path, $maxCount);
    }

    public function preview(FilePath $path, $markdown)
    {
        $markdownDocument = $this->markdownService->parse($path, $markdown);

        return $markdownDocument->getHtml();
    }

    protected function assertHasLock(User $user, $lockPath)
    {
        if ($this->gitRepository->exists($lockPath) && !$this->isLockExpired($lockPath)) {
            $lockLogin = $this->getLockLogin($lockPath);
            if ($lockLogin == $user->getEmail($user)) {
                return true;
            }
        }

        throw new PageLockExpiredException();
    }

    protected function removeLockFile($lockPath)
    {
        $this->gitRepository->remove($lockPath);
    }

    protected function getLockLogin($lockPath)
    {
        return $this->gitRepository->getContent($lockPath);
    }

    protected function getLockPath(FilePath $relativeFilePath)
    {
        $name = $relativeFilePath->getName();
        $relativeLockPath = $relativeFilePath->getParentPath()->appendFile($name . '.lock');

        return $relativeLockPath;
    }

    protected function isLockExpired($lockPath)
    {
        $expired = time() > $this->getLockExpiry($lockPath);
        if ($expired) {
            $this->removeLockFile($lockPath);
        }

        return $expired;
    }

    protected function getLockExpiry(FilePath $relativeLockPath)
    {
        $mTime = $this->gitRepository->getModificationTime($relativeLockPath);

        return $mTime + (60);
    }

    protected function getAuthor(User $user)
    {
        $name = $user->getName();
        $email = $user->getEmail();

        return "\"$name <$email>\"";
    }


}