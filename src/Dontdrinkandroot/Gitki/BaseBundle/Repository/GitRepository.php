<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Repository;

use Dontdrinkandroot\Gitki\BaseBundle\Model\CommitMetadata;
use Dontdrinkandroot\Gitki\BaseBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Path\Path;
use Dontdrinkandroot\Utils\StringUtils;
use GitWrapper\GitWrapper;
use Symfony\Component\Filesystem\Filesystem;

class GitRepository implements GitRepositoryInterface
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

    /**
     * {@inheritdoc}
     */
    public function getRepositoryPath()
    {
        return $this->repositoryPath;
    }

    /**
     * {@inheritdoc}
     */
    public function getWorkingCopyHistory($maxCount = null)
    {
        return $this->getHistory(null, $maxCount);
    }

    /**
     * {@inheritdoc}
     */
    public function getFileHistory(FilePath $path, $maxCount = null)
    {
        return $this->getHistory($path, $maxCount);
    }

    public function getHistory(FilePath $path = null, $maxCount = null)
    {
        $options = ['pretty' => 'format:' . LogParser::getFormatString()];
        if (null !== $maxCount) {
            $options['max-count'] = $maxCount;
        }
        if (null !== $path) {
            $options['p'] = $path->toRelativeString(DIRECTORY_SEPARATOR);
        }

        $workingCopy = $this->getWorkingCopy();
        $workingCopy->log($options);
        $log = $workingCopy->getOutput();

        return $this->parseLog($log);
    }

    /**
     * @param FilePath[] $paths
     */
    public function add(array $paths)
    {
        $workingCopy = $this->getWorkingCopy();
        foreach ($paths as $path) {
            $workingCopy->add($path->toRelativeString(DIRECTORY_SEPARATOR));
        }
    }

    /**
     * @param FilePath[] $paths
     */
    public function remove(array $paths)
    {
        $workingCopy = $this->getWorkingCopy();
        foreach ($paths as $path) {
            $workingCopy->rm($path->toRelativeString(DIRECTORY_SEPARATOR));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addAndCommit(GitUserInterface $author, $commitMessage, $paths)
    {
        $this->add($this->toFilePathArray($paths));
        $this->commit($author, $commitMessage);
    }

    /**
     * {@inheritdoc}
     */
    public function removeAndCommit(GitUserInterface $author, $commitMessage, $paths)
    {
        $this->remove($this->toFilePathArray($paths));
        $this->commit($author, $commitMessage);
    }

    public function commit(GitUserInterface $author, $commitMessage)
    {
        $this->getWorkingCopy()->commit(
            [
                'm'      => $commitMessage,
                'author' => $this->getAuthorString($author)
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function moveAndCommit(GitUserInterface $author, $commitMessage, FilePath $oldPath, FilePath $newPath)
    {
        $workingCopy = $this->getWorkingCopy();
        $workingCopy->mv(
            $oldPath->toRelativeString(DIRECTORY_SEPARATOR),
            $newPath->toRelativeString(DIRECTORY_SEPARATOR)
        );
        $this->commit($author, $commitMessage);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(Path $path)
    {
        $absolutePath = $this->getAbsolutePath($path);

        return $this->getFileSystem()->exists($absolutePath->toAbsoluteString(DIRECTORY_SEPARATOR));
    }

    /**
     * {@inheritdoc}
     */
    public function getAbsolutePath(Path $path)
    {
        return $path->prepend($this->getRepositoryPath());
    }

    /**
     * {@inheritdoc}
     */
    public function mkdir(DirectoryPath $relativePath)
    {
        $this->getFileSystem()->mkdir($this->getAbsolutePathString($relativePath), 0755);
    }

    /**
     * {@inheritdoc}
     */
    public function touch(FilePath $relativePath)
    {
        $this->getFileSystem()->touch($this->getAbsolutePathString($relativePath));
    }

    /**
     * {@inheritdoc}
     */
    public function putContent(FilePath $relativePath, $content)
    {
        file_put_contents($this->getAbsolutePathString($relativePath), $content);
    }

    /**
     * {@inheritdoc}
     */
    public function getContent(FilePath $relativePath)
    {
        return file_get_contents($this->getAbsolutePathString($relativePath));
    }

    /**
     * {@inheritdoc}
     */
    public function getModificationTime(Path $relativePath)
    {
        return filemtime($this->getAbsolutePathString($relativePath));
    }

    /**
     * {@inheritdoc}
     */
    public function removeFile(FilePath $relativePath)
    {
        $this->getFileSystem()->remove($this->getAbsolutePathString($relativePath));
    }

    /**
     * {@inheritdoc}
     */
    public function removeDirectory(DirectoryPath $path)
    {
        $this->getFileSystem()->remove($this->getAbsolutePathString($path));
    }

    /**
     * {@inheritdoc}
     */
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
     * @param Path $relativePath
     *
     * @return string
     */
    protected function getAbsolutePathString(Path $relativePath)
    {
        return $this->getAbsolutePath($relativePath)->toAbsoluteString(DIRECTORY_SEPARATOR);
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

    /**
     * @param GitUserInterface $user
     *
     * @return string
     */
    protected function getAuthorString(GitUserInterface $user)
    {
        $name = $user->getUsername();
        $email = $user->getEmail();

        return sprintf('"%s <%s>"', $user->getUsername(), $user->getEmail());
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
}
