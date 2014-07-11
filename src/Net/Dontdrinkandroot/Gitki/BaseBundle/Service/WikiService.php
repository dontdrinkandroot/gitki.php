<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Service;

// TODO: make sure that file is in repository

use GitWrapper\GitWrapper;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Event\PageSavedEvent;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Exception\PageLockedException;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Exception\PageLockExpiredException;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\DirectoryListing;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Security\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\Finder;

class WikiService
{

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    protected $repositoryPath;

    public function __construct($repositoryPath, EventDispatcherInterface $eventDispatcher)
    {
        if (empty($repositoryPath)) {
            throw new \Exception('Repository path must not be empty');
        }
        $this->repositoryPath = $repositoryPath;

        $this->eventDispatcher = $eventDispatcher;
    }

    public function pageExists($pagePath)
    {
        $absolutePath = $this->getAbsolutePagePath($pagePath);
        return file_exists($absolutePath);
    }


    public function createLock(User $user, $pagePath)
    {
        $lockPath = $this->getLockPath($pagePath);

        $this->assertUnlocked($user, $lockPath);

        $lockDir = dirname($lockPath);
        if (!file_exists($lockDir)) {
            mkdir($lockDir, 0755, true);
        }
        file_put_contents($lockPath, $user->getLogin());
    }

    public function removeLock(User $user, $pagePath)
    {
        $lockPath = $this->getLockPath($pagePath);
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


    public function getContent($pagePath)
    {
        $absolutePath = $this->getAbsolutePagePath($pagePath);
        if (!file_exists($absolutePath)) {
            return '';
        }

        return file_get_contents($absolutePath);
    }

    public function savePage(User $user, $pagePath, $content, $commitMessage)
    {
        $lockPath = $this->getLockPath($pagePath);
        $this->assertHasLock($user, $lockPath);

        $absolutePath = $this->getAbsolutePagePath($pagePath);
        file_put_contents($absolutePath, $content);
        $git = new GitWrapper();
        $workingCopy = $git->workingCopy($this->repositoryPath);
        $workingCopy->add($absolutePath);
        $workingCopy->commit(
            array(
                'm' => $commitMessage,
                'author' => $this->getAuthor($user)
            )
        );

        $this->eventDispatcher->dispatch(
            'ddr.gitki.wiki.page.saved',
            new PageSavedEvent($pagePath, $user->getLogin(), time(), $content, $commitMessage)
        );
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

    public function listDirectory($directoryPath)
    {
        $absolutePath = $this->getAbsoluteDirectoryPath($directoryPath);
        $files = array();
        $subDirectories = array();

        $finder = new Finder();
        $finder->in($absolutePath);
        $finder->name('*.md');
        $finder->depth(0);
        foreach ($finder->files() as $file) {
            $files[] = $file->getRelativePathname();
        }

        $finder = new Finder();
        $finder->in($absolutePath);
        $finder->depth(0);
        $finder->ignoreDotFiles(true);
        foreach ($finder->directories() as $directory) {
            $subDirectories[] = $directory->getRelativePathname();
        }

        return new DirectoryListing($directoryPath, $files, $subDirectories);
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

    protected function getAbsolutePagePath($pagePath)
    {
        $absolutePath = $this->repositoryPath . '/' . $pagePath . '.md';
        return $absolutePath;
    }

    protected function getAbsoluteDirectoryPath($directoryPath)
    {
        $absolutePath = $this->repositoryPath . '/' . $directoryPath;
        return $absolutePath;
    }

    protected function getLockPath($pagePath)
    {
        $absolutePagePath = $this->getAbsolutePagePath($pagePath);
        $lockPath = $absolutePagePath . '.lock';

        return $lockPath;
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
        return $mTime + (60 * 5);
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