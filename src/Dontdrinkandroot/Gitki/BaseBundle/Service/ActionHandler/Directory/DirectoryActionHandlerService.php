<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Service\ActionHandler\Directory;

use Dontdrinkandroot\Gitki\BaseBundle\Entity\User;
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
    public function handle(DirectoryPath $directoryPath, Request $request, User $user)
    {
        // TODO: Implement handle() method.
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
