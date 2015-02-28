<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\Directory;

use Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\AbstractContainerAwareHandler;
use Dontdrinkandroot\Gitki\WebBundle\Entity\User;
use Dontdrinkandroot\Path\DirectoryPath;
use Symfony\Component\HttpFoundation\Request;

class DirectoryIndexActionHandler extends AbstractContainerAwareHandler implements DirectoryActionHandlerInterface
{

    /**
     * {@inheritdoc}
     */
    public function handle(DirectoryPath $directoryPath, Request $request, User $user)
    {
        $indexFilePath = $directoryPath->appendFile('index.md');
        if ($this->getWikiService()->exists($indexFilePath)) {
            return $this->redirectToRoute('ddr_gitki_wiki_file', ['path' => $indexFilePath->toAbsoluteString()]);
        }

        return $this->redirectToRoute('ddr_gitki_wiki_directory', ['path' => $directoryPath->toAbsoluteString()]);
    }
}
