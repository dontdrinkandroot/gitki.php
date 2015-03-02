<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Service\ActionHandler;

use Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\File\FileActionHandlerInterface;
use Dontdrinkandroot\Gitki\BaseBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Component\HttpFoundation\Request;

class FileActionHandlerService implements FileActionHandlerServiceInterface
{

    /**
     * @var array
     */
    protected $handlers = [];

    /**
     * {@inheritdoc}
     */
    public function handle(FilePath $filePath, Request $request, GitUserInterface $user)
    {
        $action = $request->query->get('action', '');
        $extension = $filePath->getExtension();
        $handler = $this->getHandler($action, $extension);
        if (null === $handler) {
            /* Try to fallback to default extension */
            $handler = $this->getHandler($action, '');
        }

        if (null !== $handler) {
            return $handler->handle($filePath, $request, $user);
        }

        throw new \Exception('No handler for action ' . $action . ' and extension ' . $extension . ' found');
    }

    /**
     * {@inheritdoc}
     */
    public function registerHandler(FileActionHandlerInterface $handler, $action = '', $extension = '')
    {
        if (!isset($this->handlers[$action])) {
            $this->handlers[$action] = [];
        }
        $this->handlers[$action][$extension] = $handler;
    }

    /**
     * @param string $action
     * @param string $extension
     *
     * @return FileActionHandlerInterface|null
     */
    protected function getHandler($action, $extension)
    {
        if (!isset($this->handlers[$action])) {
            return null;
        }

        if (!isset($this->handlers[$action][$extension])) {
            return null;
        }

        return $this->handlers[$action][$extension];
    }
}
