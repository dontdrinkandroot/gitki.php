<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Model\FileInfo;

use Dontdrinkandroot\Path\Path;

abstract class PathAwareFileInfo extends \SplFileInfo
{

    /**
     * @return Path
     */
    abstract public function getRelativePath();

    /**
     * @return Path
     */
    abstract public function getAbsolutePath();
}
