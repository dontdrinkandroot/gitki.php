<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\File;

use Dontdrinkandroot\Gitki\BaseBundle\Entity\User;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface FileActionHandlerInterface
{

    /**
     * @param FilePath $filePath
     * @param Request  $request
     * @param User     $user
     *
     * @return Response
     */
    public function handle(FilePath $filePath, Request $request, User $user);
}
