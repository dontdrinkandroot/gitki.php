<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Repository;


use GitWrapper\GitWrapper;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\CommitMetadata;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\FilePath;

class GitRepository
{

    private $repositoryPath;

    public function __construct($repositoryPath)
    {
        $this->repositoryPath = $repositoryPath;
    }

    /**
     * @return \Net\Dontdrinkandroot\Gitki\BaseBundle\Model\CommitMetadata[]
     */
    public function getWorkingCopyHistory($maxCount = null)
    {
        $options = array('pretty' => "format:" . LogParser::getFormatString());
        if (null !== $maxCount) {
            $options['max-count'] = $maxCount;
        }

        $workingCopy = $this->getWorkingCopy();
        $workingCopy->log($options);
        $log = $workingCopy->getOutput();

        return $this->parseLog($log);
    }

    public function getFileHistory(FilePath $path, $maxCount = null)
    {

        $options = array('pretty' => "format:" . LogParser::getFormatString());
        if (null !== $maxCount) {
            $options['max-count'] = $maxCount;
        }
        $options['p'] = $path->toString();

        $workingCopy = $this->getWorkingCopy();
        $workingCopy->log($options);
        $log = $workingCopy->getOutput();

        return $this->parseLog($log);
    }

    /**
     * @param string $log
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
     * @return \GitWrapper\GitWorkingCopy
     */
    protected function getWorkingCopy()
    {
        $git = new GitWrapper();
        $workingCopy = $git->workingCopy($this->repositoryPath);

        return $workingCopy;
    }

    public function addAndCommit($author, $commitMessage, $paths)
    {
        if (!is_array($paths)) {
            $paths = array($paths);
        }

        $workingCopy = $this->getWorkingCopy();
        foreach ($paths as $path) {
            $workingCopy->add($path->toString());
        }
        $this->commit($author, $commitMessage);
    }

    public function removeAndCommit($author, $commitMessage, $paths)
    {
        if (!is_array($paths)) {
            $paths = array($paths);
        }

        $workingCopy = $this->getWorkingCopy();
        foreach ($paths as $path) {
            $workingCopy->rm($path);
        }
        $this->commit($author, $commitMessage);
    }

    public function commit($author, $commitMessage)
    {
        $this->getWorkingCopy()->commit(
            array(
                'm' => $commitMessage,
                'author' => $author
            )
        );
    }

    public function moveAndCommit($author, $commitMessage, $oldPath, $newPath)
    {
        $workingCopy = $this->getWorkingCopy();
        $workingCopy->mv($oldPath->toString(), $newPath->toString());
        $this->commit($author, $commitMessage);
    }
} 