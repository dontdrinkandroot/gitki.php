<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\Directory;

use Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\AbstractContainerAwareHandler;
use Dontdrinkandroot\Gitki\BaseBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\DirectoryPath;
use Symfony\Component\HttpFoundation\Request;

class UploadFileActionHandler extends AbstractContainerAwareHandler implements DirectoryActionHandlerInterface
{

    /**
     * {@inheritdoc}
     */
    public function handle(DirectoryPath $directoryPath, Request $request, GitUserInterface $user)
    {
        $this->assertRole('ROLE_COMMITTER');

        $form = $this->createFormBuilder()
            ->add('uploadedFile', 'file', array('label' => 'File'))
            ->add('uploadedFileName', 'text', array('label' => 'Filename (if other)', 'required' => false))
            ->add('Upload', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /* @var \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile */
                $uploadedFile = $form->get('uploadedFile')->getData();
                $uploadedFileName = $form->get('uploadedFileName')->getData();
                if (null == $uploadedFileName || trim($uploadedFileName) == "") {
                    $uploadedFileName = $uploadedFile->getClientOriginalName();
                }
                $filePath = $directoryPath->appendFile($uploadedFileName);
                $this->getWikiService()->addFile(
                    $user,
                    $filePath,
                    $uploadedFile,
                    'Adding ' . $filePath
                );

                return $this->redirect(
                    $this->generateUrl(
                        'ddr_gitki_wiki_directory',
                        array('path' => $directoryPath->toAbsoluteUrlString())
                    )
                );
            }
        } else {
        }

        return $this->render(
            'DdrGitkiBaseBundle:Wiki:directory.uploadFile.html.twig',
            array('form' => $form->createView(), 'path' => $directoryPath)
        );
    }
}
