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
     * @param string $path
     * @return Response
     */
    public function directoryAction(Request $request, $path)
    {
        $this->checkPreconditions($request, $path);

        $action = $request->query->get('action', 'list');
        switch ($action) {
            case 'index' :
                return $this->directoryIndexAction($request, $path);
            case 'upload' :
                return $this->directoryUploadAction($request, $path);
            case 'delete' :
                return $this->deleteDirectoryAction($request, $path);
            default:
                return $this->listDirectoryAction($path);
        }
    }

    /**
     * @param Request $request
     * @param string $path
     * @return Response
     */
    public function fileAction(Request $request, $path)
    {
        $this->checkPreconditions($request, $path);

        $action = $request->query->get('action', 'show');
        switch ($action) {
            case 'edit' :
                return $this->editPageAction($request, $path);
            case 'delete' :
                return $this->deleteFileAction($request, $path);
            case 'holdlock':
                return $this->holdLockAction($request, $path);
            case 'rename' :
                return $this->renameFileAction($request, $path);
            case 'history' :
                return $this->fileHistoryAction($request, $path);
            case 'preview_markdown' :
                return $this->previewMarkdownAction($request, $path);
            default:
                return $this->showFileAction($request, $path);
        }
    }

    public function showFileAction(Request $request, $path)
    {
        $filePath = FilePath::parse($path);
        if (StringUtils::endsWith($filePath->getName(), '.md')) {
            return $this->showPageAction($request, $path);
        } else {
            return $this->serveFileAction($request, $path);
        }
    }

    public function showPageAction(Request $request, $path)
    {
        $filePath = FilePath::parse($path);

        $file = null;
        try {
            $file = $this->getWikiService()->getFile($filePath);
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

        $document = $this->getWikiService()->getParsedMarkdownDocument($filePath);

        $renderedView = $this->renderView(
            'DdrGitkiBaseBundle:Wiki:page.html.twig',
            array(
                'path' => $filePath,
                'document' => $document
            )
        );

        $response->setContent($renderedView);

        return $response;
    }

    private function previewMarkdownAction(Request $request, $path)
    {
        $filePath = FilePath::parse($path);
        $markdown = $request->query->get('markdown');
        $html = $this->getWikiService()->preview($filePath, $markdown);

        return new Response($html);
    }

    public function serveFileAction(Request $request, $path)
    {
        $filePath = FilePath::parse($path);
        $file = $this->getWikiService()->getFile($filePath);

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

    public function holdLockAction(Request $request, $path)
    {
        $filePath = FilePath::parse($path);
        $expiry = $this->getWikiService()->holdLock($this->getUser(), $filePath);

        return new Response($expiry);
    }

    public function editPageAction(Request $request, $path)
    {
        $this->assertRole('ROLE_COMMITER');

        $filePath = FilePath::parse($path);
        if (!StringUtils::endsWith($filePath->getName(), '.md')) {
            throw new HttpException(500, 'Only editing of markdown files is supported');
        }

        $user = $this->getUser();

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
            $this->getWikiService()->removeLock($user, $filePath);

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
                $this->getWikiService()->savePage($user, $filePath, $content, $commitMessage);
                $this->getWikiService()->removeLock($user, $filePath);

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
            if ($this->getWikiService()->exists($filePath)) {
                $content = $this->getWikiService()->getContent($filePath);
            }

            if (!$form->isSubmitted()) {
                $form->setData(
                    array(
                        'content' => $content,
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

    public function renameFileAction(Request $request, $path)
    {
        $this->assertRole('ROLE_COMMITER');

        $filePath = FilePath::parse($path);
        $user = $this->getUser();

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
                        array('path' => $newPath->getParentPath() . '/')
                    )
                );
            }

        } else {
            $form->setData(array('newpath' => $path));
        }

        return $this->render(
            'DdrGitkiBaseBundle:Wiki:renameFile.html.twig',
            array('form' => $form->createView(), 'path' => $filePath)
        );
    }

    public function deleteFileAction(Request $request, $path)
    {
        $this->assertRole('ROLE_COMMITER');

        $filePath = FilePath::parse($path);
        $user = $this->getUser();

        $commitMessage = 'Removing ' . $filePath->toAbsoluteUrlString();
        $this->getWikiService()->deleteFile($user, $filePath, $commitMessage);

        $parentDirPath = $filePath->getParentPath()->toAbsoluteUrlString();
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

    private function fileHistoryAction(Request $request, $path)
    {
        $filePath = FilePath::parse($path);
        $history = $this->getWikiService()->getFileHistory($filePath);

        return $this->render(
            'DdrGitkiBaseBundle:Wiki:fileHistory.html.twig',
            array(
                'path' => $filePath,
                'history' => $history
            )
        );
    }

    public function directoryIndexAction(Request $request, $path)
    {
        $dirPath = DirectoryPath::parse($path);
        $indexFilePath = $dirPath->appendFile('index.md');

        if ($this->getWikiService()->exists($indexFilePath)) {
            return $this->redirect(
                $this->generateUrl('ddr_gitki_wiki_file', array('path' => $indexFilePath->toAbsoluteUrlString()))
            );
        } else {
            return $this->redirect(
                $this->generateUrl('ddr_gitki_wiki_directory', array('path' => $dirPath->toAbsoluteUrlString()))
            );
        }
    }

    public function listDirectoryAction($path)
    {
        $directoryPath = DirectoryPath::parse($path);
        $directoryListing = $this->getWikiService()->listDirectory($directoryPath);

        return $this->render(
            'DdrGitkiBaseBundle:Wiki:directoryListing.html.twig',
            array(
                'path' => $directoryPath,
                'directoryListing' => $directoryListing
            )
        );
    }

    public function directoryUploadAction(Request $request, $path)
    {
        $this->assertRole('ROLE_COMMITER');

        $directoryPath = DirectoryPath::parse($path);
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
                    $this->getUser(),
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
            'DdrGitkiBaseBundle:Wiki:directoryUpload.html.twig',
            array('form' => $form->createView(), 'path' => $directoryPath)
        );
    }

    public function deleteDirectoryAction(Request $request, $path)
    {
        $this->assertRole('ROLE_COMMITER');

        $directoryPath = DirectoryPath::parse($path);

        $this->getWikiService()->deleteDirectory($directoryPath);

        $parentDirPath = $directoryPath->getParentPath()->toAbsoluteUrlString();

        return $this->redirect(
            $this->generateUrl(
                'ddr_gitki_wiki_directory',
                array('path' => $parentDirPath)
            )
        );
    }

    public function historyAction()
    {
        $history = $this->getWikiService()->getHistory(20);

        return $this->render('DdrGitkiBaseBundle:Wiki:history.html.twig', array('history' => $history));
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

    protected function checkPreconditions($request, $path)
    {
        if (StringUtils::startsWith($path, '/.git')) {
            throw new AccessDeniedHttpException();
        }
    }


}