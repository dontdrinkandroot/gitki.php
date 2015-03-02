<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Service\ActionHandler;

use Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\Directory\DirectoryActionHandlerInterface;
use Dontdrinkandroot\Gitki\BaseBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\DirectoryPath;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface DirectoryActionHandlerServiceInterface
{

    /**
     * @param DirectoryPath $directoryPath
     * @param Request       $request
     * @param GitUserInterface $user
     *
     * @return Response
     */
    public function handle(DirectoryPath $directoryPath, Request $request, GitUserInterface $user);

    /**
     * @param DirectoryActionHandlerInterface $directoryActionHandler
     * @param string                          $action
     */
    public function registerHandler(DirectoryActionHandlerInterface $directoryActionHandler, $action);
}
