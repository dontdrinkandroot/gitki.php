<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Service;

use Dontdrinkandroot\Gitki\BaseBundle\Entity\User;
use Dontdrinkandroot\Gitki\BaseBundle\Event\MarkdownDocumentDeletedEvent;
use Dontdrinkandroot\Gitki\BaseBundle\Event\MarkdownDocumentSavedEvent;
use Dontdrinkandroot\Gitki\BaseBundle\Exception\DirectoryNotEmptyException;
use Dontdrinkandroot\Gitki\BaseBundle\Exception\FileExistsException;
use Dontdrinkandroot\Gitki\BaseBundle\Exception\PageLockedException;
use Dontdrinkandroot\Gitki\BaseBundle\Exception\PageLockExpiredException;
use Dontdrinkandroot\Gitki\BaseBundle\Model\CommitMetadata;
use Dontdrinkandroot\Gitki\BaseBundle\Model\DirectoryListing;
use Dontdrinkandroot\Gitki\BaseBundle\Model\FileInfo\Directory;
use Dontdrinkandroot\Gitki\BaseBundle\Model\FileInfo\PageFile;
use Dontdrinkandroot\Gitki\BaseBundle\Model\ParsedMarkdownDocument;
use Dontdrinkandroot\Gitki\BaseBundle\Repository\GitRepository;
use Dontdrinkandroot\Gitki\BaseBundle\Service\Markdown\MarkdownService;
use GitWrapper\GitException;
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

    /**
     * @param GitRepository            $gitRepository
     * @param MarkdownService          $markdownService
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        GitRepository $gitRepository,
        MarkdownService $markdownService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->gitRepository = $gitRepository;
        $this->markdownService = $markdownService;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Path $relativePath
     *
     * @return bool
     */
    public function exists(Path $relativePath)
    {
        return $this->gitRepository->exists($relativePath);
    }

    /**
     * @param User     $user
     * @param FilePath $relativeFilePath
     */
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

    /**
     * @param User     $user
     * @param FilePath $relativeFilePath
     *
     * @throws \Exception
     */
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

    /**
     * @param FilePath $relativeFilePath
     *
     * @return string
     */
    public function getContent(FilePath $relativeFilePath)
    {
        return $this->gitRepository->getContent($relativeFilePath);
    }

    /**
     * @param FilePath $relativeFilePath
     *
     * @return ParsedMarkdownDocument
     */
    public function getParsedMarkdownDocument(FilePath $relativeFilePath)
    {
        $content = $this->getContent($relativeFilePath);

        return $this->markdownService->parse($relativeFilePath, $content);
    }

    /**
     * @param User     $user
     * @param FilePath $relativeFilePath
     * @param string   $content
     * @param string   $commitMessage
     *
     * @throws \Exception
     */
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
            new MarkdownDocumentSavedEvent($relativeFilePath, $user, time(), $parsedMarkdownDocument, $commitMessage)
        );
    }

    /**
     * @param User     $user
     * @param FilePath $relativeFilePath
     *
     * @return int
     */
    public function holdLock(User $user, FilePath $relativeFilePath)
    {
        $lockPath = $this->getLockPath($relativeFilePath);
        $this->assertHasLock($user, $lockPath);

        $this->gitRepository->touch($lockPath);

        return $this->getLockExpiry($lockPath);
    }

    /**
     * @param User     $user
     * @param FilePath $relativeFilePath
     * @param string   $commitMessage
     *
     * @throws \Exception
     */
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
                new MarkdownDocumentDeletedEvent($relativeFilePath, $user, time(), $commitMessage)
            );
        }
    }

    /**
     * @param DirectoryPath $relativeDirectoryPath
     *
     * @throws DirectoryNotEmptyException
     */
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

    /**
     * @param User     $user
     * @param FilePath $relativeOldFilePath
     * @param FilePath $relativeNewFilePath
     * @param string   $commitMessage
     *
     * @throws FileExistsException
     * @throws \Exception
     */
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
                new MarkdownDocumentDeletedEvent($relativeOldFilePath, $user, time(), $commitMessage)
            );
        }

        if (StringUtils::endsWith($relativeNewFilePath->getName(), '.md')) {
            $content = $this->getContent($relativeNewFilePath);
            $parsedMarkdownDocument = $this->markdownService->parse($relativeNewFilePath, $content);
            $this->eventDispatcher->dispatch(
                'ddr.gitki.wiki.markdown_document.saved',
                new MarkdownDocumentSavedEvent(
                    $relativeNewFilePath,
                    $user,
                    time(),
                    $parsedMarkdownDocument,
                    $commitMessage
                )
            );
        }
    }

    /**
     * @param User         $user
     * @param FilePath     $relativeFilePath
     * @param UploadedFile $uploadedFile
     * @param string       $commitMessage
     *
     * @throws FileExistsException
     */
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

    /**
     * @param User     $user
     * @param FilePath $relativeLockPath
     *
     * @return bool
     * @throws PageLockedException
     */
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

    /**
     * @param DirectoryPath $relativeDirectoryPath
     *
     * @return DirectoryListing
     */
    public function listDirectory(DirectoryPath $relativeDirectoryPath)
    {
        $repositoryPath = $this->gitRepository->getRepositoryPath();

        /* @var PageFile[] $pages */
        $pages = array();
        /* @var Directory[] $subDirectories */
        $subDirectories = array();
        /* @var \Dontdrinkandroot\Gitki\BaseBundle\Model\FileInfo\File[] $otherFiles */
        $otherFiles = array();

        $finder = new Finder();
        $finder->in($this->gitRepository->getAbsolutePathString($relativeDirectoryPath));
        $finder->depth(0);
        foreach ($finder->files() as $file) {
            /* @var \Symfony\Component\Finder\SplFileInfo $file */
            if ($file->getExtension() == "md") {
                $pages[] = $this->createPageFile($repositoryPath, $relativeDirectoryPath, $file);
            } else {
                if ($file->getExtension() != 'lock') {
                    $otherFile = new \Dontdrinkandroot\Gitki\BaseBundle\Model\FileInfo\File(
                        $repositoryPath->toAbsoluteFileString(),
                        $relativeDirectoryPath->toRelativeFileString(),
                        $file->getRelativePathName()
                    );
                    $otherFiles[] = $otherFile;
                }
            }
        }

        $finder = new Finder();
        $finder->in($this->gitRepository->getAbsolutePathString($relativeDirectoryPath));
        $finder->depth(0);
        $finder->ignoreDotFiles(true);
        foreach ($finder->directories() as $directory) {
            /* @var \Symfony\Component\Finder\SplFileInfo $directory */
            $subDirectory = new Directory(
                $repositoryPath->toAbsoluteFileString(),
                $relativeDirectoryPath->toRelativeFileString(),
                $directory->getRelativePathName() . DIRECTORY_SEPARATOR
            );
            $subDirectories[] = $subDirectory;
        }

        usort(
            $pages,
            function (PageFile $a, PageFile $b) {
                return strcmp($a->getTitle(), $b->getTitle());
            }
        );
        usort(
            $subDirectories,
            function (Directory $a, Directory $b) {
                return strcmp($a->getFilename(), $b->getFilename());
            }
        );
        usort(
            $otherFiles,
            function (
                \Dontdrinkandroot\Gitki\BaseBundle\Model\FileInfo\File $a,
                \Dontdrinkandroot\Gitki\BaseBundle\Model\FileInfo\File $b
            ) {
                return strcmp($a->getFilename(), $b->getFilename());
            }
        );

        return new DirectoryListing($relativeDirectoryPath, $pages, $subDirectories, $otherFiles);
    }

    /**
     * @param FilePath $path
     *
     * @return File
     */
    public function getFile(FilePath $path)
    {
        $absolutePath = $this->gitRepository->getAbsolutePath($path);

        return new File($absolutePath->toAbsoluteFileString());
    }

    /**
     * @param int $maxCount
     *
     * @return CommitMetadata[]
     *
     * @throws GitException
     */
    public function getHistory($maxCount)
    {
        try {
            return $this->gitRepository->getWorkingCopyHistory($maxCount);
        } catch (GitException $e) {
            if ($e->getMessage() === "fatal: bad default revision 'HEAD'\n") {
                /* swallow, history not there yet */
                return [];
            } else {
                throw $e;
            }
        }
    }

    /**
     * @param FilePath $path
     * @param int|null $maxCount
     *
     * @return CommitMetadata[]
     */
    public function getFileHistory(FilePath $path, $maxCount = null)
    {
        return $this->gitRepository->getFileHistory($path, $maxCount);
    }

    /**
     * @param FilePath $path
     * @param string   $markdown
     *
     * @return mixed
     */
    public function preview(FilePath $path, $markdown)
    {
        $markdownDocument = $this->markdownService->parse($path, $markdown);

        return $markdownDocument->getHtml();
    }

    /**
     * @param DirectoryPath $path
     */
    public function createFolder(DirectoryPath $path)
    {
        $this->gitRepository->createFolder($path);
    }

    /**
     * @param User     $user
     * @param FilePath $lockPath
     *
     * @return bool
     *
     * @throws PageLockExpiredException
     */
    protected function assertHasLock(User $user, FilePath $lockPath)
    {
        if ($this->gitRepository->exists($lockPath) && !$this->isLockExpired($lockPath)) {
            $lockLogin = $this->getLockLogin($lockPath);
            if ($lockLogin == $user->getEmail($user)) {
                return true;
            }
        }

        throw new PageLockExpiredException();
    }

    /**
     * @param FilePath $lockPath
     */
    protected function removeLockFile(FilePath $lockPath)
    {
        $this->gitRepository->remove($lockPath);
    }

    /**
     * @param FilePath $lockPath
     *
     * @return string
     */
    protected function getLockLogin(FilePath $lockPath)
    {
        return $this->gitRepository->getContent($lockPath);
    }

    /**
     * @param FilePath $relativeFilePath
     *
     * @return FilePath
     */
    protected function getLockPath(FilePath $relativeFilePath)
    {
        $name = $relativeFilePath->getName();
        $relativeLockPath = $relativeFilePath->getParentPath()->appendFile($name . '.lock');

        return $relativeLockPath;
    }

    /**
     * @param FilePath $lockPath
     *
     * @return bool
     */
    protected function isLockExpired(FilePath $lockPath)
    {
        $expired = time() > $this->getLockExpiry($lockPath);
        if ($expired) {
            $this->removeLockFile($lockPath);
        }

        return $expired;
    }

    /**
     * @param FilePath $relativeLockPath
     *
     * @return int
     */
    protected function getLockExpiry(FilePath $relativeLockPath)
    {
        $mTime = $this->gitRepository->getModificationTime($relativeLockPath);

        return $mTime + (60);
    }

    /**
     * @param User $user
     *
     * @return string
     */
    protected function getAuthor(User $user)
    {
        $name = $user->getUsername();
        $email = $user->getEmail();

        return "\"$name <$email>\"";
    }

    /**
     * @param DirectoryPath $repositoryPath
     * @param DirectoryPath $directoryPath
     * @param SplFileInfo   $file
     *
     * @return PageFile
     */
    protected function createPageFile(DirectoryPath $repositoryPath, DirectoryPath $directoryPath, SplFileInfo $file)
    {
        $pageFile = new PageFile(
            $repositoryPath->toAbsoluteFileString(),
            $directoryPath->toRelativeFileString(),
            $file->getRelativePathName()
        );
        $pageFile->setTitle($pageFile->getRelativePath()->getFileName());

        return $pageFile;
    }
}
