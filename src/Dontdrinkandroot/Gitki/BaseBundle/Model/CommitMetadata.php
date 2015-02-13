<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Model;

class CommitMetadata
{

    /**
     * @var string
     */
    private $hash;

    /**
     * @var string
     */
    private $committer;

    /**
     * @var string
     */
    private $eMail;

    /**
     * @var int
     */
    private $timeStamp;

    /**
     * @var string
     */
    private $message;

    public function __construct($hash, $committer, $eMail, $timeStamp, $message)
    {
        $this->hash = $hash;
        $this->committer = $committer;
        $this->eMail = $eMail;
        $this->timeStamp = $timeStamp;
        $this->message = $message;
    }

    /**
     * @param string $committer
     */
    public function setCommitter($committer)
    {
        $this->committer = $committer;
    }

    /**
     * @return string
     */
    public function getCommitter()
    {
        return $this->committer;
    }

    /**
     * @param string $eMail
     */
    public function setEMail($eMail)
    {
        $this->eMail = $eMail;
    }

    /**
     * @return string
     */
    public function getEMail()
    {
        return $this->eMail;
    }

    /**
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param int $timeStamp
     */
    public function setTimeStamp($timeStamp)
    {
        $this->timeStamp = $timeStamp;
    }

    /**
     * @return int
     */
    public function getTimeStamp()
    {
        return $this->timeStamp;
    }
}
