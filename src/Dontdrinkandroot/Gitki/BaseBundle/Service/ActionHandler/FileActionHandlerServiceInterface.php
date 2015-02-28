<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Service\ActionHandler;

use Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\File\FileActionHandlerInterface;
use Dontdrinkandroot\Gitki\WebBundle\Entity\User;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface FileActionHandlerServiceInterface
{

    /**
     * @param FilePath $filePath
     * @param Request  $request
     * @param \Dontdrinkandroot\Gitki\WebBundle\Entity\User $user
     *
     * @return Response
     */
    public function handle(FilePath $filePath, Request $request, User $user);

    /**
     * @param FileActionHandlerInterface $handler
     * @param string                     $action
     * @param string                     $extension
     */
    public function registerHandler(FileActionHandlerInterface $handler, $action, $extension);
}
