<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Service\ActionHandler\File;

use Dontdrinkandroot\Gitki\BaseBundle\Entity\User;
use Dontdrinkandroot\Gitki\BaseBundle\Service\ActionHandler\AbstractContainerAwareHandler;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Component\HttpFoundation\Request;

class FileHistoryActionHandler extends AbstractContainerAwareHandler implements FileActionHandlerInterface
{

    /**
     * {@inheritdoc}
     */
    public function handle(FilePath $filePath, Request $request, User $user)
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
