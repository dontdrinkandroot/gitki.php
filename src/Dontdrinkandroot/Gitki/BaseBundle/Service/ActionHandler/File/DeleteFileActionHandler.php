<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Service\ActionHandler\File;

use Dontdrinkandroot\Gitki\BaseBundle\Entity\User;
use Dontdrinkandroot\Gitki\BaseBundle\Service\ActionHandler\AbstractContainerAwareHandler;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Component\HttpFoundation\Request;

class DeleteFileActionHandler extends AbstractContainerAwareHandler implements FileActionHandlerInterface
{

    /**
     * {@inheritdoc}
     */
    public function handle(FilePath $filePath, Request $request, User $user)
    {
        $this->assertRole('ROLE_COMMITTER');

        $commitMessage = 'Removing ' . $filePath->toAbsoluteUrlString();
        $this->getWikiService()->deleteFile($user, $filePath, $commitMessage);

        return $this->redirect(
            $this->generateUrl(
                'ddr_gitki_wiki_directory',
                array('path' => $filePath->getParentPath()->toAbsoluteUrlString())
            )
        );
    }
}
