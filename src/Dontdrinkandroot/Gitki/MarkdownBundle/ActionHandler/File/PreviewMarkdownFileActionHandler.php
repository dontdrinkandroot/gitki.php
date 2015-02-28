<?php

namespace Dontdrinkandroot\Gitki\MarkdownBundle\ActionHandler\File;

use Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\AbstractContainerAwareHandler;
use Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\File\FileActionHandlerInterface;
use Dontdrinkandroot\Gitki\WebBundle\Entity\User;
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
