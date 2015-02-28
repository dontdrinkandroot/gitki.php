<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Controller;

use Dontdrinkandroot\Gitki\BaseBundle\Service\ActionHandler\DirectoryActionHandlerServiceInterface;
use Dontdrinkandroot\Gitki\BaseBundle\Service\ActionHandler\FileActionHandlerServiceInterface;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Utils\StringUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
        $directoryHandlerService = $this->getDirectoryActionHandlerService();

        return $directoryHandlerService->handle($directoryPath, $request, $this->getUser());
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
        $fileHandlerService = $this->getFileHandlerService();

        return $fileHandlerService->handle($filePath, $request, $this->getUser());
    }

    /**
     * @return Response
     * @throws \Exception
     * @throws \GitWrapper\GitException
     */
    public function historyAction()
    {
        $history = $this->getWikiService()->getHistory(20);

        return $this->render('DdrGitkiBaseBundle:Wiki:history.html.twig', ['history' => $history]);
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

    /**
     * @return DirectoryActionHandlerServiceInterface
     */
    protected function getDirectoryActionHandlerService()
    {
        return $this->get('ddr.gitki.service.action_handler.directory');
    }

    /**
     * @return FileActionHandlerServiceInterface
     */
    protected function getFileHandlerService()
    {
        return $this->get('ddr.gitki.service.action_handler.file');
    }
}
