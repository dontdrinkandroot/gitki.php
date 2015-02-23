<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Service\ActionHandler\File;

use Dontdrinkandroot\Gitki\BaseBundle\Entity\User;
use Dontdrinkandroot\Gitki\BaseBundle\Service\ActionHandler\AbstractContainerAwareHandler;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PreviewMarkdownFileActionHandler extends AbstractContainerAwareHandler implements FileActionHandlerInterface
{

    /**
     * {@inheritdoc}
     */
    public function handle(FilePath $filePath, Request $request, User $user)
    {
        $markdown = $request->request->get('markdown');
        $html = $this->getWikiService()->preview($filePath, $markdown);

        return new Response($html);
    }
}
