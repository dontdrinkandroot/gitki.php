<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Service\ActionHandler;

use Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\File\FileActionHandlerInterface;
use Dontdrinkandroot\Gitki\BaseBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface FileActionHandlerServiceInterface
{

    /**
     * @param FilePath $filePath
     * @param Request  $request
     * @param GitUserInterface $user
     *
     * @return Response
     */
    public function handle(FilePath $filePath, Request $request, GitUserInterface $user);

    /**
     * @param FileActionHandlerInterface $handler
     * @param string                     $action
     * @param string                     $extension
     */
    public function registerHandler(FileActionHandlerInterface $handler, $action, $extension);
}
