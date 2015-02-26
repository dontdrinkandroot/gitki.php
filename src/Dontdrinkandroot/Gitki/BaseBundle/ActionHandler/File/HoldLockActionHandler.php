<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\File;

use Dontdrinkandroot\Gitki\BaseBundle\Entity\User;
use Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\AbstractContainerAwareHandler;
use Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\File\FileActionHandlerInterface;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HoldLockActionHandler extends AbstractContainerAwareHandler implements FileActionHandlerInterface
{

    /**
     * {@inheritdoc}
     */
    public function handle(FilePath $filePath, Request $request, User $user)
    {
        $expiry = $this->getWikiService()->holdLock($user, $filePath);

        return new Response($expiry);
    }
}
