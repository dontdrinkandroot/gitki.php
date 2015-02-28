<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\Directory;

use Dontdrinkandroot\Gitki\WebBundle\Entity\User;
use Dontdrinkandroot\Path\DirectoryPath;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface DirectoryActionHandlerInterface
{

    /**
     * @param DirectoryPath $directoryPath
     * @param Request       $request
     * @param User          $user
     *
     * @return Response
     */
    public function handle(DirectoryPath $directoryPath, Request $request, User $user);
}
