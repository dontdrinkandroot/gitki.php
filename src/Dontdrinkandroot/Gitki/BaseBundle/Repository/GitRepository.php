<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Repository;

use Dontdrinkandroot\Gitki\BaseBundle\Model\CommitMetadata;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Path\Path;
use Dontdrinkandroot\Utils\StringUtils;
use GitWrapper\GitWrapper;
use Symfony\Component\Filesystem\Filesystem;

class GitRepository
{

    /**
     * @var Filesystem
     */
    protected $fileSystem = null;

    private $repositoryPath;

    public function __construct($repositoryPath)
    {
        $pathString = $repositoryPath;

        if (!StringUtils::startsWith($pathString, '/')) {
            throw new \Exception('Repository Path must be absolute');
        }

        if (!StringUtils::endsWith($pathString, '/')) {
            $pathString .= '/';
        }

        $this->repositoryPath = DirectoryPath::parse($pathString);
    }

    public function getRepositoryPath()
    {
        return $this->repositoryPath;
    }

    /**
     * @param int|null $maxCount
     *
     * @return CommitMetadata[]
     */
    public function getWorkingCopyHistory($maxCount = null)
    {
        return $this->getHistory(null, $maxCount);
    }

    /**
     * @param FilePath $path
     * @param int|null $maxCount
     *
     * @return CommitMetadata[]
     */
    public function getFileHistory(FilePath $path, $maxCount = null)
    {
        return $this->getHistory($path, $maxCount);
    }

    public function getHistory(FilePath $path = null, $maxCount = null)
    {
        $options = array('pretty' => 'format:' . LogParser::getFormatString());
        if (null !== $maxCount) {
            $options['max-count'] = $maxCount;
        }
        if (null !== $path) {
            $options['p'] = $path->toRelativeFileString();
        }

        $workingCopy = $this->getWorkingCopy();
        $workingCopy->log($options);
        $log = $workingCopy->getOutput();

        return $this->parseLog($log);
    }

    /**
     * @param string $log
     *
     * @return CommitMetadata[]
     */
    protected function parseLog($log)
    {
        preg_match_all(LogParser::getMatchString(), $log, $matches);
        $metaData = array();
        for ($i = 0; $i < count($matches[1]); $i++) {
            $hash = $matches[1][$i];
            $name = $matches[2][$i];
            $eMail = $matches[3][$i];
            $timeStamp = (int)$matches[4][$i];
            $message = $matches[5][$i];
            $metaData[] = new CommitMetadata($hash, $name, $eMail, $timeStamp, $message);
        }

        return $metaData;
    }

    /**
     * @param FilePath[] $paths
     */
    public function add(array $paths)
    {
        $workingCopy = $this->getWorkingCopy();
        foreach ($paths as $path) {
            $workingCopy->add($path->toRelativeFileString());
        }
    }

    /**
     * @param FilePath[] $paths
     */
    public function remove(array $paths)
    {
        $workingCopy = $this->getWorkingCopy();
        foreach ($paths as $path) {
            $workingCopy->rm($path->toRelativeFileString());
        }
    }

    /**
     * @param string              $author
     * @param string              $commitMessage
     * @param FilePath[]|FilePath $paths
     */
    public function addAndCommit($author, $commitMessage, $paths)
    {
        $this->add($this->toFilePathArray($paths));
        $this->commit($author, $commitMessage);
    }

    /**
     * @param string              $author
     * @param string              $commitMessage
     * @param FilePath[]|FilePath $paths
     */
    public function removeAndCommit($author, $commitMessage, $paths)
    {
        $this->remove($this->toFilePathArray($paths));
        $this->commit($author, $commitMessage);
    }

    public function commit($author, $commitMessage)
    {
        $this->getWorkingCopy()->commit(
            array(
                'm'      => $commitMessage,
                'author' => $author
            )
        );
    }

    public function moveAndCommit($author, $commitMessage, FilePath $oldPath, FilePath $newPath)
    {
        $workingCopy = $this->getWorkingCopy();
        $workingCopy->mv($oldPath->toRelativeFileString(), $newPath->toRelativeFileString());
        $this->commit($author, $commitMessage);
    }

    public function exists(Path $path)
    {
        $absolutePath = $this->getAbsolutePath($path);

        return $this->getFileSystem()->exists($absolutePath->toAbsoluteFileString());
    }

    public function getAbsolutePath(Path $path)
    {
        return $path->prepend($this->getRepositoryPath());
    }

    public function getAbsolutePathString(Path $relativePath)
    {
        return $this->getAbsolutePath($relativePath)->toAbsoluteFileString();
    }

    /**
     * @return Filesystem
     */
    protected function getFileSystem()
    {
        if (null === $this->fileSystem) {
            $this->fileSystem = new Filesystem();
        }

        return $this->fileSystem;
    }

    public function mkdir(DirectoryPath $relativePath)
    {
        $this->getFileSystem()->mkdir($this->getAbsolutePathString($relativePath), 0755);
    }

    public function touch(FilePath $relativePath)
    {
        $this->getFileSystem()->touch($this->getAbsolutePathString($relativePath));
    }

    public function putContent(FilePath $relativePath, $content)
    {
        file_put_contents($this->getAbsolutePathString($relativePath), $content);
    }

    public function getContent(FilePath $relativePath)
    {
        return file_get_contents($this->getAbsolutePathString($relativePath));
    }

    public function getModificationTime(Path $relativePath)
    {
        return filemtime($this->getAbsolutePathString($relativePath));
    }

    public function removeFile($relativePath)
    {
        unlink($this->getAbsolutePathString($relativePath));
    }

    public function createFolder(DirectoryPath $path)
    {
        $this->getFileSystem()->mkdir($this->getAbsolutePathString($path));
    }

    /**
     * @return \GitWrapper\GitWorkingCopy
     */
    protected function getWorkingCopy()
    {
        $git = new GitWrapper();
        $workingCopy = $git->workingCopy($this->repositoryPath);

        return $workingCopy;
    }

    /**
     * @param FilePath[]|FilePath $paths
     *
     * @return FilePath[]
     */
    protected function toFilePathArray($paths)
    {
        if (!is_array($paths)) {
            return [$paths];
        } else {
            return $paths;
        }
    }
}
