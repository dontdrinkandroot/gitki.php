<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\File;

use Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\AbstractContainerAwareHandler;
use Dontdrinkandroot\Gitki\BaseBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Component\HttpFoundation\Request;

class FileHistoryActionHandler extends AbstractContainerAwareHandler implements FileActionHandlerInterface
{

    /**
     * {@inheritdoc}
     */
    public function handle(FilePath $filePath, Request $request, GitUserInterface $user)
    {
        $history = $this->getWikiService()->getFileHistory($filePath);

        return $this->render(
            'DdrGitkiBaseBundle:Wiki:file.history.html.twig',
            array(
                'path'    => $filePath,
                'history' => $history
            )
        );
    }
}
