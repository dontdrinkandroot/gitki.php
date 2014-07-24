<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Service;

// TODO: make sure that file is in repository

use Net\Dontdrinkandroot\Gitki\BaseBundle\Event\MarkdownDocumentDeletedEvent;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Event\MarkdownDocumentSavedEvent;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Exception\DirectoryNotEmptyException;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Exception\FileExistsException;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Exception\PageLockedException;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Exception\PageLockExpiredException;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\DirectoryListing;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\Path\DirectoryPath;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\Path\FilePath;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\Path\Path;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Repository\GitRepository;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Security\User;
use Net\Dontdrinkandroot\Symfony\ExtensionBundle\Utils\StringUtils;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class WikiService
{

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    protected $repositoryPath;

    /**
     * @var \Net\Dontdrinkandroot\Gitki\BaseBundle\Repository\GitRepository
     */
    protected $gitRepository;

    public function __construct(
        GitRepository $gitRepository,
        $repositoryPath,
        EventDispatcherInterface $eventDispatcher
    ) {
        if (empty($repositoryPath)) {
            throw new \Exception('Repository path must not be empty');
        }
        $this->repositoryPath = $repositoryPath;

        $this->gitRepository = $gitRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function pageExists(FilePath $path)
    {
        $absolutePath = $this->getAbsolutePath($path);

        return file_exists($absolutePath);
    }


    public function createLock(User $user, FilePath $path)
    {
        $lockPath = $this->getAbsoluteLockPath($path);

        $this->assertUnlocked($user, $lockPath);

        $fileSystem = new Filesystem();
        $lockDir = dirname($lockPath);
        if (!$fileSystem->exists($lockDir)) {
            $fileSystem->mkdir($lockDir, 0755);
        }

        if ($fileSystem->exists($lockPath)) {
            $fileSystem->touch($lockPath);
        } else {
            file_put_contents($lockPath, $user->getLogin());
        }
    }

    public function removeLock(User $user, FilePath $path)
    {
        $lockPath = $this->getAbsoluteLockPath($path);
        if (!file_exists($lockPath)) {
            return;
        }

        if ($this->isLockExpired($lockPath)) {
            return;
        }

        $lockLogin = $this->getLockLogin($lockPath);
        if ($lockLogin != $user->getLogin()) {
            throw new \Exception('Cannot remove lock of different user');
        }

        $this->removeLockFile($lockPath);
    }


    public function getContent(FilePath $path)
    {
        $absolutePath = $this->getAbsolutePath($path);
        if (!file_exists($absolutePath)) {
            return '';
        }

        return file_get_contents($absolutePath);
    }

    public function savePage(User $user, FilePath $path, $content, $commitMessage)
    {
        $lockPath = $this->getAbsoluteLockPath($path);
        $this->assertHasLock($user, $lockPath);

        $absolutePath = $this->getAbsolutePath($path);
        file_put_contents($absolutePath, $content);

        $this->gitRepository->addAndCommit($this->getAuthor($user), $commitMessage, $path);

        $this->eventDispatcher->dispatch(
            'ddr.gitki.wiki.markdown_document.saved',
            new MarkdownDocumentSavedEvent($path, $user->getLogin(), time(), $content, $commitMessage)
        );
    }

    public function holdLock(User $user, FilePath $path)
    {
        $lockPath = $this->getAbsoluteLockPath($path);
        $this->assertHasLock($user, $lockPath);
        $fileSystem = new Filesystem();
        $fileSystem->touch($lockPath);

        return $this->getLockExpiry($lockPath);
    }

    public function deleteFile(User $user, FilePath $path)
    {
        $this->createLock($user, $path);

        $commitMessage = 'Removing ' . $path->toUrlString();
        $this->gitRepository->removeAndCommit($this->getAuthor($user), $commitMessage, $path);

        $this->removeLock($user, $path);

        if (StringUtils::endsWith($path->getName(), '.md')) {
            $this->eventDispatcher->dispatch(
                'ddr.gitki.wiki.markdown_document.deleted',
                new MarkdownDocumentDeletedEvent($path, $user->getLogin(), time(), $commitMessage)
            );
        }
    }

    public function deleteDirectory(User $user, DirectoryPath $path)
    {
        $absolutePath = $this->getAbsolutePath($path);
        $finder = new Finder();
        $finder->in($absolutePath);
        $numFiles = $finder->files()->count();
        if ($numFiles > 0) {
            throw new DirectoryNotEmptyException($path . ' is not empty');
        }

        $fileSystem = new Filesystem();
        $fileSystem->remove($absolutePath);
    }

    public function renameFile(User $user, FilePath $oldPath, FilePath $newPath, $commitMessage)
    {
        $absoluteNewPath = $this->getAbsolutePath($newPath);

        $fileSystem = new Filesystem();
        if ($fileSystem->exists($absoluteNewPath)) {
            throw new FileExistsException('File ' . $newPath . ' already exists');
        }

        $oldLockPath = $this->getAbsoluteLockPath($oldPath);

        $this->assertHasLock($user, $oldLockPath);
        $this->createLock($user, $newPath);

        $this->gitRepository->moveAndCommit($this->getAuthor($user), $commitMessage, $oldPath, $newPath);

        $this->removeLock($user, $oldPath);
        $this->removeLock($user, $newPath);

        if (StringUtils::endsWith($oldPath->getName(), '.md')) {
            $this->eventDispatcher->dispatch(
                'ddr.gitki.wiki.markdown_document.deleted',
                new MarkdownDocumentDeletedEvent($oldPath, $user->getLogin(), time(), $commitMessage)
            );
        }

        if (StringUtils::endsWith($newPath->getName(), '.md')) {
            $content = $this->getContent($newPath);
            $this->eventDispatcher->dispatch(
                'ddr.gitki.wiki.markdown_document.saved',
                new MarkdownDocumentSavedEvent($newPath, $user->getLogin(), time(), $content, $commitMessage)
            );
        }
    }

    public function addFile(User $user, FilePath $path, UploadedFile $file, $commitMessage)
    {
        $directoryPath = $path->getParentPath();
        $absoluteFilePath = $this->getAbsolutePath($path);
        $absoluteDirectoryPath = $this->getAbsolutePath($directoryPath);

        $fileSystem = new Filesystem();
        if ($fileSystem->exists($absoluteFilePath)) {
            throw new FileExistsException('File ' . $path . ' already exists');
        }

        if (!$fileSystem->exists($absoluteDirectoryPath)) {
            $fileSystem->mkdir($absoluteDirectoryPath, 0755);
        }

        $this->createLock($user, $path);
        $file->move($absoluteDirectoryPath, $path->getName());

        $this->gitRepository->addAndCommit($this->getAuthor($user), $commitMessage, $path);

        $this->removeLock($user, $path);
    }

    public function assertUnlocked(User $user, $lockPath)
    {
        if (!file_exists($lockPath)) {
            return true;
        }

        if ($this->isLockExpired($lockPath)) {
            return true;
        }

        $lockLogin = $this->getLockLogin($lockPath);
        if ($lockLogin == $user->getLogin()) {
            return true;
        }

        throw new PageLockedException($lockLogin, $this->getLockExpiry($lockPath));
    }

    /**
     * @return FilePath[]
     */
    public function findAllMarkdownFiles()
    {
        $finder = new Finder();
        $finder->in($this->repositoryPath);
        $finder->name('*.md');

        $filePaths = array();

        foreach ($finder->files() as $file) {
            /** @var SplFileInfo $file */
            $filePaths[] = FilePath::parse($file->getRelativePathname());
        }

        return $filePaths;
    }


    public function listDirectory(DirectoryPath $path)
    {
        $absolutePath = $this->getAbsolutePath($path);
        /* @var SplFileInfo[] $pages */
        $pages = array();
        /* @var SplFileInfo[] $subDirectories */
        $subDirectories = array();
        /* @var SplFileInfo[] $otherFiles */
        $otherFiles = array();

        $finder = new Finder();
        $finder->in($absolutePath);
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
        $finder->in($absolutePath);
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

        return new DirectoryListing($path, $pages, $subDirectories, $otherFiles);
    }

    /**
     * @param FilePath $path
     * @return File
     */
    public function getFile(FilePath $path)
    {
        $absolutePath = $this->getAbsolutePath($path);

        return new File($absolutePath);
    }

    public function getHistory($maxCount)
    {
        return $this->gitRepository->getWorkingCopyHistory($maxCount);
    }

    public function getFileHistory(FilePath $path, $maxCount = null)
    {
        return $this->gitRepository->getFileHistory($path, $maxCount);
    }

    protected function assertHasLock(User $user, $lockPath)
    {
        if (file_exists($lockPath) && !$this->isLockExpired($lockPath)) {
            $lockLogin = $this->getLockLogin($lockPath);
            if ($lockLogin == $user->getLogin($user)) {
                return true;
            }
        }

        throw new PageLockExpiredException();
    }

    protected function removeLockFile($lockPath)
    {
        unlink($lockPath);
    }

    protected function getLockLogin($lockPath)
    {
        return file_get_contents($lockPath);
    }

    protected function getAbsolutePath(Path $path)
    {
        $absolutePath = $this->repositoryPath;
        if ($path != null) {
            $absolutePath .= $path->toUrlString();
        }

        return $absolutePath;
    }

    protected function getAbsoluteLockPath(FilePath $path)
    {
        $lockPath = $path->getParentPath()->appendFile($path->getName() . '.lock');
        $absoluteLockPath = $this->repositoryPath . $lockPath->toUrlString();

        return $absoluteLockPath;
    }

    protected function isLockExpired($lockPath)
    {
        $expired = time() > $this->getLockExpiry($lockPath);
        if ($expired) {
            $this->removeLockFile($lockPath);
        }

        return $expired;
    }

    protected function getLockExpiry($lockPath)
    {
        $mTime = filemtime($lockPath);

        return $mTime + (60);
    }

    protected function getAuthor(User $user)
    {
        $name = $user->getLogin();
        if (null != $user->getRealName()) {
            $name = $user->getRealName();
        }
        $email = $user->getPrimaryEMail();

        return "\"$name <$email>\"";
    }

}