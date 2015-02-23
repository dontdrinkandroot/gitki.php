<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Service\ActionHandler\Directory;

use Dontdrinkandroot\Gitki\BaseBundle\Entity\User;
use Dontdrinkandroot\Gitki\BaseBundle\Service\ActionHandler\AbstractHandler;
use Dontdrinkandroot\Path\DirectoryPath;
use Symfony\Component\HttpFoundation\Request;

class ListDirectoryActionHandler extends AbstractHandler implements DirectoryActionHandlerInterface
{

    /**
     * {@inheritdoc}
     */
    public function handle(DirectoryPath $directoryPath, Request $request, User $user)
    {
        $directoryListing = $this->getWikiService()->listDirectory($directoryPath);

        return $this->render(
            'DdrGitkiBaseBundle:Wiki:directory.listing.html.twig',
            array(
                'path' => $directoryPath,
                'directoryListing' => $directoryListing
            )
        );
    }
}
