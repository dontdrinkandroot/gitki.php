<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Service;

// TODO: make sure that file is in repository

use GitWrapper\GitWrapper;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Security\User;

class WikiService
{

    protected $repositoryPath;

    public function __construct($repositoryPath)
    {
        if (empty($repositoryPath)) {
            throw new \Exception('Repository path must not be empty');
        }
        $this->repositoryPath = $repositoryPath;
    }

    public function pageExists($pagePath)
    {
        $absolutePath = $this->getAbsolutePath($pagePath);
        return file_exists($absolutePath);
    }

    public function isLocked(User $user, $pagePath)
    {
        $lockPath = $this->getLockPath($pagePath);
        if (!file_exists($lockPath)) {
            return false;
        }

        if ($this->isLockExpired($lockPath)) {
            $this->removeLockFile($lockPath);
            return false;
        }

        $lockLogin = $this->getLockLogin($lockPath);
        return ($lockLogin != $user->getLogin());
    }

    public function createLock(User $user, $pagePath)
    {
        $lockPath = $this->getLockPath($pagePath);
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
            $this->removeLockFile($lockPath);
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
        $absolutePath = $this->getAbsolutePath($pagePath);
        if (!file_exists($absolutePath)) {
            return '';
        }

        return file_get_contents($absolutePath);
    }

    public function savePage(User $user, $pagePath, $content, $commitMessage)
    {
        $absolutePath = $this->getAbsolutePath($pagePath);
        file_put_contents($absolutePath, $content);
        $git = new GitWrapper();
        $workingCopy = $git->workingCopy($this->repositoryPath);
        $workingCopy->add($absolutePath);
        $workingCopy->commit(
            array(
                'm' => $commitMessage,
                'author' => '"' . $user->getRealName() . ' <' . $user->getPrimaryEMail() . '>' . '"'
            )
        );
    }

    protected function removeLockFile($lockPath)
    {
        unlink($lockPath);
    }

    protected function getLockLogin($lockPath)
    {
        return file_get_contents($lockPath);
    }

    protected function getAbsolutePath($pagePath)
    {
        $absolutePath = $this->repositoryPath . '/' . $pagePath . '.md';
        return $absolutePath;
    }

    protected function getLockPath($pagePath)
    {
        $absolutePagePath = $this->getAbsolutePath($pagePath);
        $lockPath = $absolutePagePath . '.lock';

        return $lockPath;
    }

    private function isLockExpired($lockPath)
    {
        $mTime = filemtime($lockPath);
        return ((time() - $mTime) > 60 * 5);
    }


}