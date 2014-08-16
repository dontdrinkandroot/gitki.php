<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Controller;


use GitWrapper\GitException;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Exception\PageLockedException;
use Net\Dontdrinkandroot\Utils\Path\DirectoryPath;
use Net\Dontdrinkandroot\Utils\Path\FilePath;
use Net\Dontdrinkandroot\Utils\StringUtils;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WikiController extends BaseController
{

    /**
     * @param Request $request
     * @param string  $path
     *
     * @return Response
     */
    public function directoryAction(Request $request, $path)
    {
        $this->checkPreconditions($request, $path);

        $directoryPath = DirectoryPath::parse($path);

        $action = $request->query->get('action', 'list');
        switch ($action) {
            case 'index' :
                return $this->directoryIndexAction($request, $directoryPath);
            case 'upload' :
                return $this->directoryUploadAction($request, $directoryPath);
            case 'delete' :
                return $this->deleteDirectoryAction($request, $directoryPath);
            case 'addpage' :
                return $this->addPageAction($request, $directoryPath);
            case 'addfolder' :
                return $this->addFolderAction($request, $directoryPath);
            default:
                return $this->listDirectoryAction($request, $directoryPath);
        }
    }

    /**
     * @param Request $request
     * @param string  $path
     *
     * @return Response
     */
    public function fileAction(Request $request, $path)
    {
        $this->checkPreconditions($request, $path);

        $filePath = FilePath::parse($path);

        $action = $request->query->get('action', 'show');
        switch ($action) {
            case 'edit' :
                return $this->editPageAction($request, $filePath);
            case 'delete' :
                return $this->deleteFileAction($request, $filePath);
            case 'holdlock':
                return $this->holdLockAction($request, $filePath);
            case 'rename' :
                return $this->renameFileAction($request, $filePath);
            case 'history' :
                return $this->fileHistoryAction($request, $filePath);
            case 'preview_markdown' :
                return $this->previewMarkdownAction($request, $filePath);
            default:
                return $this->showFileAction($request, $filePath);
        }
    }

    /**
     * @param Request  $request
     * @param FilePath $path
     *
     * @return Response
     */
    public function showFileAction(Request $request, FilePath $path)
    {
        if (StringUtils::endsWith($path->getName(), '.md')) {
            return $this->showPageAction($request, $path);
        } else {
            return $this->serveFileAction($request, $path);
        }
    }

    /**
     * @param Request  $request
     * @param FilePath $path
     *
     * @return Response
     * @throws NotFoundHttpException
     */
    public function showPageAction(Request $request, FilePath $path)
    {
        $file = null;
        try {
            $file = $this->getWikiService()->getFile($path);
        } catch (FileNotFoundException $e) {

            if (null === $this->getUser()) {
                throw new NotFoundHttpException('This page does not exist');
            }

            return $this->redirect(
                $this->generateUrl(
                    'ddr_gitki_wiki_file',
                    array('path' => $path, 'action' => 'edit')
                )
            );
        }

        $response = new Response();
        $lastModified = new \DateTime();
        $lastModified->setTimestamp($file->getMTime());
        $response->setLastModified($lastModified);
        if ($this->getEnvironment() === 'prod' && $response->isNotModified($request)) {
            return $response;
        }

        $document = $this->getWikiService()->getParsedMarkdownDocument($path);

        $renderedView = $this->renderView(
            'DdrGitkiBaseBundle:Wiki:page.html.twig',
            array(
                'path' => $path,
                'document' => $document
            )
        );

        $response->setContent($renderedView);

        return $response;
    }

    /**
     * @param Request  $request
     * @param FilePath $path
     *
     * @return Response
     */
    public function previewMarkdownAction(Request $request, FilePath $path)
    {
        $markdown = $request->query->get('markdown');
        $html = $this->getWikiService()->preview($path, $markdown);

        return new Response($html);
    }

    /**
     * @param Request  $request
     * @param FilePath $path
     *
     * @return Response
     */
    public function serveFileAction(Request $request, FilePath $path)
    {
        $file = $this->getWikiService()->getFile($path);

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

    /**
     * @param Request  $request
     * @param FilePath $path
     *
     * @return Response
     */
    public function holdLockAction(Request $request, FilePath $path)
    {
        $expiry = $this->getWikiService()->holdLock($this->getUser(), $path);

        return new Response($expiry);
    }

    /**
     * @param Request  $request
     * @param FilePath $path
     *
     * @return Response
     * @throws \Exception
     * @throws GitException
     * @throws HttpException
     */
    public function editPageAction(Request $request, FilePath $path)
    {
        $this->assertRole('ROLE_COMMITER');

        if (!StringUtils::endsWith($path->getName(), '.md')) {
            throw new HttpException(500, 'Only editing of markdown files is supported');
        }

        $user = $this->getUser();

        try {
            $this->getWikiService()->createLock($user, $path);
        } catch (PageLockedException $e) {
            $renderedView = $this->renderView(
                'DdrGitkiBaseBundle:Wiki:page.locked.html.twig',
                array('path' => $path, 'lockedBy' => $e->getLockedBy())
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
                        'save' => array('type' => 'submit', 'options' => array('label' => 'Save')),
                        'cancel' => array(
                            'type' => 'submit',
                            'options' => array('label' => 'Cancel', 'attr' => array('type' => 'default'))
                        ),
                    )
                )
            )
            ->getForm();

        $form->handleRequest($request);

        /** @var SubmitButton $cancelButton */
        $cancelButton = $form->get('actions')->get('cancel');
        if ($cancelButton->isClicked()) {
            $this->getWikiService()->removeLock($user, $path);

            return $this->redirect(
                $this->generateUrl(
                    'ddr_gitki_wiki_file',
                    array('path' => $path)
                )
            );
        }

        if ($form->isValid()) {

            $content = $form->get('content')->getData();
            $commitMessage = $form->get('commitMessage')->getData();
            try {
                $this->getWikiService()->savePage($user, $path, $content, $commitMessage);
                $this->getWikiService()->removeLock($user, $path);

                return $this->redirect(
                    $this->generateUrl(
                        'ddr_gitki_wiki_file',
                        array('path' => $path)
                    )
                );
            } catch (GitException $e) {
                throw $e;
            }

        } else {

            $content = null;
            if ($this->getWikiService()->exists($path)) {
                $content = $this->getWikiService()->getContent($path);
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
                        'content' => $content,
                        'commitMessage' => 'Editing ' . $path->toAbsoluteUrlString()
                    )
                );
            }
        }

        return $this->render(
            'DdrGitkiBaseBundle:Wiki:page.edit.html.twig',
            array('form' => $form->createView(), 'path' => $path)
        );
    }

    /**
     * @param Request  $request
     * @param FilePath $path
     *
     * @return Response
     * @throws ConflictHttpException
     */
    public function renameFileAction(Request $request, FilePath $path)
    {
        $this->assertRole('ROLE_COMMITER');

        $user = $this->getUser();

        try {
            $this->getWikiService()->createLock($user, $path);
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
                    $path,
                    $newPath,
                    'Renaming ' . $path->toAbsoluteUrlString() . ' to ' . $newPath->toAbsoluteUrlString()
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

        return $this->render(
            'DdrGitkiBaseBundle:Wiki:renameFile.html.twig',
            array('form' => $form->createView(), 'path' => $path)
        );
    }


    /**
     * @param Request  $request
     * @param FilePath $path
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteFileAction(Request $request, FilePath $path)
    {
        $this->assertRole('ROLE_COMMITER');

        $user = $this->getUser();

        $commitMessage = 'Removing ' . $path->toAbsoluteUrlString();
        $this->getWikiService()->deleteFile($user, $path, $commitMessage);

        $parentDirPath = $path->getParentPath()->toAbsoluteUrlString();
        if ($parentDirPath == "") {
            $parentDirPath = "/";
        }

        return $this->redirect(
            $this->generateUrl(
                'ddr_gitki_wiki_directory',
                array('path' => $parentDirPath)
            )
        );
    }

    /**
     * @param Request  $request
     * @param FilePath $path
     *
     * @return Response
     */
    public function fileHistoryAction(Request $request, FilePath $path)
    {
        $history = $this->getWikiService()->getFileHistory($path);

        return $this->render(
            'DdrGitkiBaseBundle:Wiki:fileHistory.html.twig',
            array(
                'path' => $path,
                'history' => $history
            )
        );
    }

    /**
     * @param Request       $request
     * @param DirectoryPath $path
     *
     * @return RedirectResponse
     */
    public function directoryIndexAction(Request $request, DirectoryPath $path)
    {
        $indexFilePath = $path->appendFile('index.md');

        if ($this->getWikiService()->exists($indexFilePath)) {
            return $this->redirect(
                $this->generateUrl('ddr_gitki_wiki_file', array('path' => $indexFilePath->toAbsoluteUrlString()))
            );
        } else {
            return $this->redirect(
                $this->generateUrl('ddr_gitki_wiki_directory', array('path' => $path->toAbsoluteUrlString()))
            );
        }
    }

    /**
     * @param Request       $request
     * @param DirectoryPath $path
     *
     * @return Response
     */
    public function listDirectoryAction(Request $request, DirectoryPath $path)
    {
        $directoryListing = $this->getWikiService()->listDirectory($path);

        return $this->render(
            'DdrGitkiBaseBundle:Wiki:directoryListing.html.twig',
            array(
                'path' => $path,
                'directoryListing' => $directoryListing
            )
        );
    }

    /**
     * @param Request       $request
     * @param DirectoryPath $path
     *
     * @return Response
     */
    public function directoryUploadAction(Request $request, DirectoryPath $path)
    {
        $this->assertRole('ROLE_COMMITER');

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
                $filePath = $path->appendFile($uploadedFileName);
                $this->getWikiService()->addFile(
                    $this->getUser(),
                    $filePath,
                    $uploadedFile,
                    'Adding ' . $filePath
                );

                return $this->redirect(
                    $this->generateUrl(
                        'ddr_gitki_wiki_directory',
                        array('path' => $path->toAbsoluteUrlString())
                    )
                );
            }
        } else {
        }

        return $this->render(
            'DdrGitkiBaseBundle:Wiki:directoryUpload.html.twig',
            array('form' => $form->createView(), 'path' => $path)
        );
    }

    /**
     * @param Request       $request
     * @param DirectoryPath $directoryPath
     *
     * @return RedirectResponse
     */
    public function deleteDirectoryAction(Request $request, DirectoryPath $directoryPath)
    {
        $this->assertRole('ROLE_COMMITER');

        $this->getWikiService()->deleteDirectory($directoryPath);

        $parentDirPath = $directoryPath->getParentPath()->toAbsoluteUrlString();

        return $this->redirect(
            $this->generateUrl(
                'ddr_gitki_wiki_directory',
                array('path' => $parentDirPath)
            )
        );
    }

    /**
     * @param Request       $request
     * @param DirectoryPath $path
     *
     * @return Response
     */
    public function addPageAction(Request $request, DirectoryPath $path)
    {
        $this->assertRole('ROLE_COMMITER');

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
                $filePath = $path->appendFile($filename);

                return $this->redirect(
                    $this->generateUrl(
                        'ddr_gitki_wiki_file',
                        array('path' => $filePath->toAbsoluteUrlString(), 'action' => 'edit', 'title' => $title)
                    )
                );
            }

        }

        return $this->render(
            'DdrGitkiBaseBundle:Wiki:addPage.html.twig',
            array('form' => $form->createView(), 'path' => $path)
        );
    }

    /**
     * @param Request       $request
     * @param DirectoryPath $path
     *
     * @return Response
     */
    public function addFolderAction(Request $request, DirectoryPath $path)
    {
        $this->assertRole('ROLE_COMMITER');

        $path = DirectoryPath::parse($path);

        $form = $this->createFormBuilder()
            ->add('title', 'text', array('label' => 'Title', 'required' => true))
            ->add(
                'dirname',
                'text',
                array(
                    'label' => 'Foldername',
                    'required' => true,
                )
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
                        array('path' => $subDirPath->toAbsoluteUrlString())
                    )
                );
            }

        }

        return $this->render(
            'DdrGitkiBaseBundle:Wiki:addFolder.html.twig',
            array('form' => $form->createView(), 'path' => $path)
        );
    }

    /**
     * @return Response
     */
    public function historyAction()
    {
        $history = $this->getWikiService()->getHistory(20);

        return $this->render('DdrGitkiBaseBundle:Wiki:history.html.twig', array('history' => $history));
    }

    /**
     * @param File $file
     *
     * @return string
     * @throws \RuntimeException
     */
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

    /**
     * @param Request $request
     * @param string  $path
     *
     * @throws AccessDeniedHttpException
     */
    protected function checkPreconditions(Request $request, $path)
    {
        if (StringUtils::startsWith($path, '/.git')) {
            throw new AccessDeniedHttpException();
        }
    }


}