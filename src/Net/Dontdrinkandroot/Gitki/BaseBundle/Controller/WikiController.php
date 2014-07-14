<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Controller;


use GitWrapper\GitException;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Exception\PageLockedException;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\DirectoryPath;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\FilePath;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WikiController extends BaseController
{

    public function listDirectoryAction($path = '')
    {
        $locator = new DirectoryPath($path);
        $directoryListing = $this->getWikiService()->listDirectory($locator);
        return $this->render(
            'DdrGitkiBaseBundle:Wiki:directoryListing.html.twig',
            array(
                'path' => $path,
                'locator' => $locator,
                'directoryListing' => $directoryListing
            )
        );
    }

    public function pageAction($path)
    {
        $locator = new FilePath($path);

        if (!$this->getWikiService()->pageExists($locator)) {

            if (null === $this->getUser()) {
                throw new NotFoundHttpException('This page does not exist');
            }

            return $this->redirect(
                $this->generateUrl(
                    'ddr_gitki_wiki_page_edit',
                    array('path' => $path)
                )
            );
        }

        $content = $this->getWikiService()->getContent($locator);
        $content = $this->getMarkdownParser()->transformMarkdown($content);

        $heading = null;
        $body = null;
        if (preg_match("#<h1.*?>(.*?)</h1>#i", $content, $matches)) {
            $heading = $matches[1];
        }
        $body = preg_replace("#<h1.*</h1>#i", "", $content);

        return $this->render(
            'DdrGitkiBaseBundle:Wiki:page.html.twig',
            array(
                'heading' => $heading,
                'body' => $body,
                'path' => $path,
                'locator' => $locator
            )
        );
    }

    public function pageEditAction(Request $request, $path)
    {
        $locator = new FilePath($path);
        $user = $this->getUser();

        try {
            $this->getWikiService()->createLock($user, $locator);
        } catch (PageLockedException $e) {
            throw new ConflictHttpException($e->getMessage());
        }
        $content = $this->getWikiService()->getContent($locator);

        $form = $this->createFormBuilder()
            ->add('content', 'textarea')
            ->add('commitMessage', 'text', array('label' => 'Commit Message', 'required' => true))
            ->add('save', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $content = $form->get('content')->getData();
            $commitMessage = $form->get('commitMessage')->getData();
            try {
                $this->getWikiService()->savePage($user, $locator, $content, $commitMessage);
                $this->getWikiService()->removeLock($user, $locator);
                return $this->redirect(
                    $this->generateUrl(
                        'ddr_gitki_wiki_page',
                        array('path' => $path)
                    )
                );
            } catch (GitException $e) {
                throw $e;
            }

        } else {

            if (!$form->isSubmitted()) {
                $form->setData(
                    array(
                        'content' => $content,
                        'commitMessage' => 'Editing ' . $path
                    )
                );
            }
        }

        return $this->render('DdrGitkiBaseBundle:Wiki:pageedit.html.twig', array('form' => $form->createView()));
    }

    public function renameFileAction(Request $request, $path)
    {
        $locator = new FilePath($path);
        $user = $this->getUser();

        try {
            $this->getWikiService()->createLock($user, $locator);
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
                $newPath = new FilePath($form->get('newpath')->getData());
                $this->getWikiService()->renameFile(
                    $user,
                    $locator,
                    $newPath,
                    'Renaming ' . $locator . ' to ' . $newPath
                );

                return $this->redirect(
                    $this->generateUrl(
                        'ddr_gitki_wiki_directory',
                        array('path' => $newPath->getParentPath() . '/')
                    )
                );
            }

        } else {
            $form->setData(array('newpath' => $path));
        }

        return $this->render('DdrGitkiBaseBundle:Wiki:renameFile.html.twig', array('form' => $form->createView()));
    }

    public function deletePageAction($path)
    {
        $locator = new FilePath($path);
        $user = $this->getUser();

        $this->getWikiService()->deletePage($user, $locator);

        return $this->redirect(
            $this->generateUrl(
                'ddr_gitki_wiki_directory',
                array('path' => $locator->getParentPath()->toString())
            )
        );
    }

    public function directoryUploadAction(Request $request, $path)
    {
        $directoryPath = new DirectoryPath($path);
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
                $filePath = $directoryPath->addFile($uploadedFileName);
                $this->getWikiService()->addFile(
                    $this->getUser(),
                    $filePath,
                    $uploadedFile,
                    'Adding ' . $filePath
                );

                return $this->redirect(
                    $this->generateUrl(
                        'ddr_gitki_wiki_directory',
                        array('path' => $directoryPath->toString())
                    )
                );
            }
        } else {
        }

        return $this->render('DdrGitkiBaseBundle:Wiki:directoryUpload.html.twig', array('form' => $form->createView()));
    }

    public function showFileAction(Request $request, $path)
    {
        $locator = new FilePath($path);
        $file = $this->getWikiService()->getFile($locator);

        $response = new Response();
        $lastModified = new \DateTime();
        $lastModified->setTimestamp($file->getMTime());
        $response->setLastModified($lastModified);
        if ($response->isNotModified($request)) {
            return $response;
        }

        $response->headers->set('Content-Type', $file->getMimeType());
        $response->setContent($this->getContents($file));

        return $response;
    }

    protected function getContents(File $file)
    {
        $level = error_reporting(0);
        $content = file_get_contents($file->getPathname());
        error_reporting($level);
        if (false === $content) {
            $error = error_get_last();
            throw new \RuntimeException($error['message']);
        }

        return $content;
    }

}