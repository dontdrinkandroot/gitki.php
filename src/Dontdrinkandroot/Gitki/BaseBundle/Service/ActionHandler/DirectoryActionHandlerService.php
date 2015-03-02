<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Service\ActionHandler;

use Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\Directory\DirectoryActionHandlerInterface;
use Dontdrinkandroot\Gitki\BaseBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\DirectoryPath;
use Symfony\Component\HttpFoundation\Request;

class DirectoryActionHandlerService implements DirectoryActionHandlerServiceInterface
{

    /**
     * @var DirectoryActionHandlerInterface[]
     */
    protected $handlers = [];

    /**
     * {@inheritdoc}
     */
    public function handle(DirectoryPath $directoryPath, Request $request, GitUserInterface $user)
    {
        $action = $request->query->get('action', '');
        if (isset($this->handlers[$action])) {
            return $this->handlers[$action]->handle($directoryPath, $request, $user);
        }

        throw new \Exception('No handler for action ' . $action . ' found');
    }

    /**
     * {@inheritdoc}
     */
    public function registerHandler(DirectoryActionHandlerInterface $handler, $action)
    {
        $this->handlers[$action] = $handler;
    }
}
