<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\File;

use Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\AbstractContainerAwareHandler;
use Dontdrinkandroot\Gitki\BaseBundle\Exception\PageLockedException;
use Dontdrinkandroot\Gitki\BaseBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class RenameFileActionHandler extends AbstractContainerAwareHandler implements FileActionHandlerInterface
{

    /**
     * {@inheritdoc}
     */
    public function handle(FilePath $filePath, Request $request, GitUserInterface $user)
    {
        $this->assertRole('ROLE_COMMITTER');

        try {
            $this->getWikiService()->createLock($user, $filePath);
        } catch (PageLockedException $e) {
            throw new ConflictHttpException($e->getMessage());
        }

        $form = $this->createFormBuilder()
            ->add('newpath', 'text', array('label' => 'New path', 'required' => true))
            ->add('rename', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $newPath = FilePath::parse($form->get('newpath')->getData());
                $this->getWikiService()->renameFile(
                    $user,
                    $filePath,
                    $newPath,
                    'Renaming ' . $filePath->toAbsoluteUrlString() . ' to ' . $newPath->toAbsoluteUrlString()
                );

                return $this->redirect(
                    $this->generateUrl(
                        'ddr_gitki_wiki_directory',
                        array('path' => $newPath->getParentPath()->toAbsoluteUrlString())
                    )
                );
            }
        } else {
            $form->setData(array('newpath' => $filePath->toAbsoluteUrlString()));
        }

        return $this->render(
            'DdrGitkiBaseBundle:Wiki:file.rename.html.twig',
            array('form' => $form->createView(), 'path' => $filePath)
        );
    }
}
