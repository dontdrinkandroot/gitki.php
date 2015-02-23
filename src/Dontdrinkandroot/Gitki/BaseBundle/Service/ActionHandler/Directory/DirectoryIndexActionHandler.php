<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Service\ActionHandler\Directory;

use Dontdrinkandroot\Gitki\BaseBundle\Entity\User;
use Dontdrinkandroot\Gitki\BaseBundle\Service\ActionHandler\AbstractHandler;
use Dontdrinkandroot\Path\DirectoryPath;
use Symfony\Component\HttpFoundation\Request;

class DirectoryIndexActionHandler extends AbstractHandler implements DirectoryActionHandlerInterface
{

    /**
     * {@inheritdoc}
     */
    public function handle(DirectoryPath $directoryPath, Request $request, User $user)
    {
        $indexFilePath = $directoryPath->appendFile('index.md');
        if ($this->getWikiService()->exists($indexFilePath)) {
            return $this->redirect(
                $this->generateUrl('ddr_gitki_wiki_file', ['path' => $indexFilePath->toAbsoluteUrlString()])
            );
        }

        return $this->redirect(
            $this->generateUrl('ddr_gitki_wiki_directory', ['path' => $directoryPath->toAbsoluteUrlString()])
        );
    }
}
