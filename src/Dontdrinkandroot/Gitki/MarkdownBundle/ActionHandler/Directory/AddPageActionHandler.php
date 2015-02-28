<?php

namespace Dontdrinkandroot\Gitki\MarkdownBundle\ActionHandler\Directory;

use Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\AbstractContainerAwareHandler;
use Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\Directory\DirectoryActionHandlerInterface;
use Dontdrinkandroot\Gitki\WebBundle\Entity\User;
use Dontdrinkandroot\Path\DirectoryPath;
use Symfony\Component\HttpFoundation\Request;

class AddPageActionHandler extends AbstractContainerAwareHandler implements DirectoryActionHandlerInterface
{

    /**
     * {@inheritdoc}
     */
    public function handle(DirectoryPath $directoryPath, Request $request, User $user)
    {
        $this->assertRole('ROLE_COMMITTER');

        $form = $this->createFormBuilder()
            ->add('title', 'text', array('label' => 'Title', 'required' => true))
            ->add(
                'filename',
                'text',
                array(
                    'label' => 'Filename',
                    'required' => true,
                    'attr' => array(
                        'input_group' => array('append' => '.md')
                    )
                )
            )
            ->add('create', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $title = $form->get('title')->getData();
                $filename = $form->get('filename')->getData() . '.md';
                $filePath = $directoryPath->appendFile($filename);

                return $this->redirect(
                    $this->generateUrl(
                        'ddr_gitki_wiki_file',
                        array('path' => $filePath->toAbsoluteUrlString(), 'action' => 'edit', 'title' => $title)
                    )
                );
            }
        }

        return $this->render(
            'DdrGitkiBaseBundle:Wiki:directory.addPage.html.twig',
            array('form' => $form->createView(), 'path' => $directoryPath)
        );
    }
}
