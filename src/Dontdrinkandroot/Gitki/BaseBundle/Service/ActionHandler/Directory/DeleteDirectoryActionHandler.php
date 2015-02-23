<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Service\ActionHandler\Directory;

use Dontdrinkandroot\Gitki\BaseBundle\Entity\User;
use Dontdrinkandroot\Gitki\BaseBundle\Service\ActionHandler\AbstractContainerAwareHandler;
use Dontdrinkandroot\Path\DirectoryPath;
use Symfony\Component\HttpFoundation\Request;

class DeleteDirectoryActionHandler extends AbstractContainerAwareHandler implements DirectoryActionHandlerInterface
{

    /**
     * {@inheritdoc}
     */
    public function handle(DirectoryPath $directoryPath, Request $request, User $user)
    {
        $this->assertRole('ROLE_COMMITTER');

        $this->getWikiService()->deleteDirectory($directoryPath);

        $parentDirPath = $directoryPath->getParentPath()->toAbsoluteUrlString();

        return $this->redirect(
            $this->generateUrl(
                'ddr_gitki_wiki_directory',
                ['path' => $parentDirPath]
            )
        );
    }
}