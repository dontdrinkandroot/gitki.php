<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Service\ActionHandler\File;

use Dontdrinkandroot\Gitki\BaseBundle\Entity\User;
use Dontdrinkandroot\Gitki\BaseBundle\Service\ActionHandler\AbstractContainerAwareHandler;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ServeMarkdownFileActionHandler extends AbstractContainerAwareHandler implements FileActionHandlerInterface
{

    /**
     * {@inheritdoc}
     */
    public function handle(FilePath $filePath, Request $request, User $user)
    {
        $file = null;
        try {
            $file = $this->getWikiService()->getFile($filePath);
        } catch (FileNotFoundException $e) {
            if (null === $user) {
                throw new NotFoundHttpException('This page does not exist');
            }

            return $this->redirect(
                $this->generateUrl(
                    'ddr_gitki_wiki_file',
                    array('path' => $filePath, 'action' => 'edit')
                )
            );
        }

        $response = new Response();
        $lastModified = new \DateTime();
        $lastModified->setTimestamp($file->getMTime());
        $response->setLastModified($lastModified);

        $document = $this->getWikiService()->getParsedMarkdownDocument($filePath);

        $renderedView = $this->renderView(
            'DdrGitkiBaseBundle:Wiki:page.html.twig',
            array(
                'path'     => $filePath,
                'document' => $document
            )
        );

        $response->setContent($renderedView);

        return $response;
    }
}