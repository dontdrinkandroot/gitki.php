<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\File;

use Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\AbstractContainerAwareHandler;
use Dontdrinkandroot\Gitki\BaseBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Component\HttpFoundation\Request;

class DeleteFileActionHandler extends AbstractContainerAwareHandler implements FileActionHandlerInterface
{

    /**
     * {@inheritdoc}
     */
    public function handle(FilePath $filePath, Request $request, GitUserInterface $user)
    {
        $this->assertRole('ROLE_COMMITTER');

        $commitMessage = 'Removing ' . $filePath->toAbsoluteString();
        $this->getWikiService()->deleteFile($user, $filePath, $commitMessage);

        return $this->redirectToRoute(
            'ddr_gitki_wiki_directory',
            ['path' => $filePath->getParentPath()->toAbsoluteString()]
        );
    }
}
