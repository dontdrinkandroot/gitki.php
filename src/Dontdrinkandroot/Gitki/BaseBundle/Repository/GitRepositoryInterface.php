<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Repository;

use Dontdrinkandroot\Gitki\BaseBundle\Model\CommitMetadata;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Path\Path;
use FOS\UserBundle\Model\UserInterface;

interface GitRepositoryInterface
{

    /**
     * @param Path $path
     *
     * @return bool
     */
    public function exists(Path $path);

    /**
     * @param DirectoryPath $path
     */
    public function mkdir(DirectoryPath $path);

    /**
     * @param FilePath $path
     */
    public function touch(FilePath $path);

    /**
     * @param FilePath $path
     * @param mixed    $content
     */
    public function putContent(FilePath $path, $content);

    /**
     * @param FilePath $path
     *
     * @return string
     */
    public function getContent(FilePath $path);

    /**
     * @param UserInterface       $author
     * @param string              $commitMessage
     * @param FilePath[]|FilePath $paths
     */
    public function addAndCommit(UserInterface $author, $commitMessage, $paths);

    /**
     * @param UserInterface       $author
     * @param string              $commitMessage
     * @param FilePath[]|FilePath $paths
     */
    public function removeAndCommit(UserInterface $author, $commitMessage, $paths);

    /**
     * @param Path $path
     *
     * @return Path
     */
    public function getAbsolutePath(Path $path);

    /**
     * @param UserInterface $author
     * @param string        $commitMessage
     * @param FilePath      $oldPath
     * @param FilePath      $newPath
     */
    public function moveAndCommit(UserInterface $author, $commitMessage, FilePath $oldPath, FilePath $newPath);

    /**
     * @return DirectoryPath
     */
    public function getRepositoryPath();

    /**
     * @param int|null $maxCount
     *
     * @return CommitMetadata[]
     */
    public function getWorkingCopyHistory($maxCount);

    /**
     * @param FilePath $path
     * @param int|null $maxCount
     *
     * @return CommitMetadata[]
     */
    public function getFileHistory(FilePath $path, $maxCount);

    /**
     * @param DirectoryPath $path
     */
    public function createFolder(DirectoryPath $path);

    /**
     * @param FilePath $path
     */
    public function removeFile(FilePath $path);

    /**
     * @param Path $path
     *
     * @return int
     */
    public function getModificationTime(Path $path);

    /**
     * @param DirectoryPath $path
     */
    public function removeDirectory(DirectoryPath $path);
}
