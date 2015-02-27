<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Service;

use Dontdrinkandroot\Gitki\BaseBundle\Entity\User;
use Dontdrinkandroot\Gitki\BaseBundle\Exception\DirectoryNotEmptyException;
use Dontdrinkandroot\Gitki\BaseBundle\Exception\FileExistsException;
use Dontdrinkandroot\Gitki\BaseBundle\Exception\PageLockedException;
use Dontdrinkandroot\Gitki\BaseBundle\Exception\PageLockExpiredException;
use Dontdrinkandroot\Gitki\BaseBundle\Model\CommitMetadata;
use Dontdrinkandroot\Gitki\BaseBundle\Model\DirectoryListing;
use Dontdrinkandroot\Gitki\BaseBundle\Model\FileInfo\Directory;
use Dontdrinkandroot\Gitki\BaseBundle\Model\FileInfo\PageFile;
use Dontdrinkandroot\Gitki\BaseBundle\Model\ParsedMarkdownDocument;
use Dontdrinkandroot\Gitki\BaseBundle\Repository\GitRepositoryInterface;
use Dontdrinkandroot\Gitki\MarkdownBundle\Service\MarkdownService;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Path\Path;
use FOS\UserBundle\Model\UserInterface;
use GitWrapper\GitException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class WikiService
{

    /**
     * @var GitRepositoryInterface
     */
    protected $gitRepository;

    /**
     * @var MarkdownService
     */
    protected $markdownService;

    /**
     * @param GitRepositoryInterface $gitRepository
     * @param MarkdownService        $markdownService
     */
    public function __construct(
        GitRepositoryInterface $gitRepository,
        MarkdownService $markdownService
    ) {
        $this->gitRepository = $gitRepository;
        $this->markdownService = $markdownService;
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
     * @param UserInterface $user
     * @param FilePath      $relativeFilePath
     *
     * @throws \Exception
     */
    public function removeLock(UserInterface $user, FilePath $relativeFilePath)
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
     * @param UserInterface $user
     * @param FilePath      $relativeFilePath
     * @param string        $content
     * @param string        $commitMessage
     *
     * @return ParsedMarkdownDocument
     *
     * @throws \Exception
     */
    public function savePage(UserInterface $user, FilePath $relativeFilePath, $content, $commitMessage)
    {
        if (empty($commitMessage)) {
            throw new \Exception('Commit message must not be empty');
        }

        $relativeLockPath = $this->getLockPath($relativeFilePath);
        $this->assertHasLock($user, $relativeLockPath);

        $this->gitRepository->putContent($relativeFilePath, $content);

        $this->gitRepository->addAndCommit($user, $commitMessage, $relativeFilePath);

        $parsedMarkdownDocument = $this->markdownService->parse($relativeFilePath, $content);

        return $parsedMarkdownDocument;
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
     * @param UserInterface $user
     * @param FilePath $relativeFilePath
     * @param string   $commitMessage
     *
     * @throws \Exception
     */
    public function deleteFile(UserInterface $user, FilePath $relativeFilePath, $commitMessage)
    {
        if (empty($commitMessage)) {
            throw new \Exception('Commit message must not be empty');
        }

        $this->createLock($user, $relativeFilePath);

        $this->gitRepository->removeAndCommit($user, $commitMessage, $relativeFilePath);

        $this->removeLock($user, $relativeFilePath);
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
        $finder->in($absoluteDirectoryPath->toAbsoluteString(DIRECTORY_SEPARATOR));
        $numFiles = $finder->files()->count();
        if ($numFiles > 0) {
            throw new DirectoryNotEmptyException(
                $relativeDirectoryPath->toRelativeString(DIRECTORY_SEPARATOR) . ' is not empty'
            );
        }

        $fileSystem = new Filesystem();
        $fileSystem->remove($absoluteDirectoryPath);
    }

    /**
     * @param UserInterface $user
     * @param FilePath      $relativeOldFilePath
     * @param FilePath      $relativeNewFilePath
     * @param string        $commitMessage
     *
     * @throws FileExistsException
     * @throws \Exception
     */
    public function renameFile(
        UserInterface $user,
        FilePath $relativeOldFilePath,
        FilePath $relativeNewFilePath,
        $commitMessage
    ) {
        if ($this->gitRepository->exists($relativeNewFilePath)) {
            throw new FileExistsException(
                'File ' . $relativeNewFilePath->toRelativeString(DIRECTORY_SEPARATOR) . ' already exists'
            );
        }

        if (empty($commitMessage)) {
            throw new \Exception('Commit message must not be empty');
        }

        $oldLockPath = $this->getLockPath($relativeOldFilePath);

        $this->assertHasLock($user, $oldLockPath);
        $this->createLock($user, $relativeNewFilePath);

        $this->gitRepository->moveAndCommit(
            $user,
            $commitMessage,
            $relativeOldFilePath,
            $relativeNewFilePath
        );

        $this->removeLock($user, $relativeOldFilePath);
        $this->removeLock($user, $relativeNewFilePath);
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
            throw new FileExistsException(
                'File ' . $relativeFilePath->toRelativeString(DIRECTORY_SEPARATOR) . ' already exists'
            );
        }

        if (!$this->gitRepository->exists($relativeDirectoryPath)) {
            $this->gitRepository->mkdir($relativeDirectoryPath);
        }

        $this->createLock($user, $relativeFilePath);
        $uploadedFile->move(
            $this->gitRepository->getAbsolutePath($relativeDirectoryPath),
            $relativeFilePath->getName()
        );

        $this->gitRepository->addAndCommit($user, $commitMessage, $relativeFilePath);

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
        $finder->in($this->gitRepository->getRepositoryPath()->toAbsoluteString(DIRECTORY_SEPARATOR));
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
        $absoluteDirectoryPath = $this->gitRepository->getAbsolutePath($relativeDirectoryPath);

        /* @var PageFile[] $pages */
        $pages = array();
        /* @var Directory[] $subDirectories */
        $subDirectories = array();
        /* @var \Dontdrinkandroot\Gitki\BaseBundle\Model\FileInfo\File[] $otherFiles */
        $otherFiles = array();

        $finder = new Finder();
        $finder->in($absoluteDirectoryPath->toAbsoluteString(DIRECTORY_SEPARATOR));
        $finder->depth(0);
        foreach ($finder->files() as $file) {
            /* @var \Symfony\Component\Finder\SplFileInfo $file */
            if ($file->getExtension() == "md") {
                $pages[] = $this->createPageFile($repositoryPath, $relativeDirectoryPath, $file);
            } else {
                if ($file->getExtension() != 'lock') {
                    $otherFile = new \Dontdrinkandroot\Gitki\BaseBundle\Model\FileInfo\File(
                        $repositoryPath->toAbsoluteString(DIRECTORY_SEPARATOR),
                        $relativeDirectoryPath->toRelativeString(DIRECTORY_SEPARATOR),
                        $file->getRelativePathName()
                    );
                    $otherFiles[] = $otherFile;
                }
            }
        }

        $finder = new Finder();
        $finder->in($absoluteDirectoryPath->toAbsoluteString(DIRECTORY_SEPARATOR));
        $finder->depth(0);
        $finder->ignoreDotFiles(true);
        foreach ($finder->directories() as $directory) {
            /* @var \Symfony\Component\Finder\SplFileInfo $directory */
            $subDirectory = new Directory(
                $repositoryPath->toAbsoluteString(DIRECTORY_SEPARATOR),
                $relativeDirectoryPath->toRelativeString(DIRECTORY_SEPARATOR),
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

        return new File($absolutePath->toAbsoluteString(DIRECTORY_SEPARATOR));
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
     * @param UserInterface $user
     * @param FilePath      $lockPath
     *
     * @return bool
     *
     * @throws PageLockExpiredException
     */
    protected function assertHasLock(UserInterface $user, FilePath $lockPath)
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
        $this->gitRepository->removeFile($lockPath);
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
     * @param DirectoryPath $repositoryPath
     * @param DirectoryPath $directoryPath
     * @param SplFileInfo   $file
     *
     * @return PageFile
     */
    protected function createPageFile(DirectoryPath $repositoryPath, DirectoryPath $directoryPath, SplFileInfo $file)
    {
        $pageFile = new PageFile(
            $repositoryPath->toAbsoluteString(DIRECTORY_SEPARATOR),
            $directoryPath->toRelativeString(DIRECTORY_SEPARATOR),
            $file->getRelativePathName()
        );
        $pageFile->setTitle($pageFile->getRelativePath()->getFileName());

        return $pageFile;
    }
}
