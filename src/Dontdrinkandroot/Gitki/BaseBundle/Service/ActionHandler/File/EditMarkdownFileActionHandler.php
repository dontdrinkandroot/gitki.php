<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Service\ActionHandler\File;

use Dontdrinkandroot\Gitki\BaseBundle\Entity\User;
use Dontdrinkandroot\Gitki\BaseBundle\Exception\PageLockedException;
use Dontdrinkandroot\Gitki\BaseBundle\Service\ActionHandler\AbstractContainerAwareHandler;
use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Utils\StringUtils;
use GitWrapper\GitException;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EditMarkdownFileActionHandler extends AbstractContainerAwareHandler implements FileActionHandlerInterface
{

    /**
     * {@inheritdoc}
     */
    public function handle(FilePath $filePath, Request $request, User $user)
    {
        $this->assertRole('ROLE_COMMITTER');

        if (!StringUtils::endsWith($filePath->getName(), '.md')) {
            throw new HttpException(500, 'Only editing of markdown files is supported');
        }

        try {
            $this->getWikiService()->createLock($user, $filePath);
        } catch (PageLockedException $e) {
            $renderedView = $this->renderView(
                'DdrGitkiBaseBundle:Wiki:page.locked.html.twig',
                array('path' => $filePath, 'lockedBy' => $e->getLockedBy())
            );

            return new Response($renderedView, 409);
        }

        $form = $this->createFormBuilder()
            ->add('content', 'textarea')
            ->add('commitMessage', 'text', array('label' => 'Commit Message', 'required' => true))
            ->add(
                'actions',
                'form_actions',
                array(
                    'buttons' => array(
                        'save'   => array('type' => 'submit', 'options' => array('label' => 'Save')),
                        'cancel' => array(
                            'type'    => 'submit',
                            'options' => array('label' => 'Cancel', 'button_class' => 'default')
                        ),
                    )
                )
            )
            ->getForm();

        $form->handleRequest($request);

        /** @var SubmitButton $cancelButton */
        $cancelButton = $form->get('actions')->get('cancel');
        if ($cancelButton->isClicked()) {
            $this->getWikiService()->removeLock($user, $filePath);

            return $this->redirect(
                $this->generateUrl(
                    'ddr_gitki_wiki_file',
                    array('path' => $filePath)
                )
            );
        }

        if ($form->isValid()) {
            $content = $form->get('content')->getData();
            $commitMessage = $form->get('commitMessage')->getData();
            try {
                $this->getWikiService()->savePage($user, $filePath, $content, $commitMessage);
                $this->getWikiService()->removeLock($user, $filePath);

                return $this->redirect(
                    $this->generateUrl(
                        'ddr_gitki_wiki_file',
                        array('path' => $filePath)
                    )
                );
            } catch (GitException $e) {
                throw $e;
            }
        } else {
            $content = null;
            if ($this->getWikiService()->exists($filePath)) {
                $content = $this->getWikiService()->getContent($filePath);
            } else {
                $title = $request->query->get('title');
                if (!empty($title)) {
                    $content = $title . "\n";
                    for ($i = 0; $i < strlen($title); $i++) {
                        $content .= '=';
                    }
                    $content .= "\n\n";
                }
            }

            if (!$form->isSubmitted()) {
                $form->setData(
                    array(
                        'content'       => $content,
                        'commitMessage' => 'Editing ' . $filePath->toAbsoluteUrlString()
                    )
                );
            }
        }

        return $this->render(
            'DdrGitkiBaseBundle:Wiki:page.edit.html.twig',
            array('form' => $form->createView(), 'path' => $filePath)
        );
    }
}
