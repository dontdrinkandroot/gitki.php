<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\File;

use Dontdrinkandroot\Gitki\WebBundle\Entity\User;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface FileActionHandlerInterface
{

    /**
     * @param FilePath $filePath
     * @param Request  $request
     * @param \Dontdrinkandroot\Gitki\WebBundle\Entity\User $user
     *
     * @return Response
     */
    public function handle(FilePath $filePath, Request $request, User $user);
}
