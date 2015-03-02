<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\Directory;

use Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\AbstractContainerAwareHandler;
use Dontdrinkandroot\Gitki\BaseBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\DirectoryPath;
use Symfony\Component\HttpFoundation\Request;

class AddFolderDirectoryActionHandler extends AbstractContainerAwareHandler implements DirectoryActionHandlerInterface
{

    /**
     * {@inheritdoc}
     */
    public function handle(DirectoryPath $directoryPath, Request $request, GitUserInterface $user)
    {
        $this->assertRole('ROLE_COMMITTER');

        $path = DirectoryPath::parse($directoryPath);

        $form = $this->createFormBuilder()
            ->add('title', 'text', ['label' => 'Title', 'required' => true])
            ->add(
                'dirname',
                'text',
                [
                    'label'    => 'Foldername',
                    'required' => true,
                ]
            )
            ->add('create', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $title = $form->get('title')->getData();
                $dirname = $form->get('dirname')->getData();
                $subDirPath = $path->appendDirectory($dirname);

                $this->getWikiService()->createFolder($subDirPath);

                return $this->redirect(
                    $this->generateUrl(
                        'ddr_gitki_wiki_directory',
                        ['path' => $subDirPath->toAbsoluteUrlString()]
                    )
                );
            }
        }

        return $this->render(
            'DdrGitkiBaseBundle:Wiki:directory.addFolder.html.twig',
            ['form' => $form->createView(), 'path' => $path]
        );
    }
}
