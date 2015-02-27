<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Exception;

use Dontdrinkandroot\Path\DirectoryPath;

class DirectoryNotEmptyException extends \Exception
{
    public function __construct(DirectoryPath $directoryPath)
    {
        parent::__construct($directoryPath->toRelativeString(DIRECTORY_SEPARATOR) . ' is not empty');
    }
}
