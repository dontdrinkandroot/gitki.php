<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Model\FileInfo;


use Net\Dontdrinkandroot\Utils\Path\Path;

abstract class PathAwareFileInfo extends \SplFileInfo
{

    /**
     * @return Path
     */
    public abstract function getRelativePath();

    /**
     * @return Path
     */
    public abstract function getAbsolutePath();

} 