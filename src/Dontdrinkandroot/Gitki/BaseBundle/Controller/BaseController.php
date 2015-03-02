<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Controller;

use Dontdrinkandroot\Gitki\BaseBundle\Model\GitUserInterface;
use Dontdrinkandroot\Gitki\BaseBundle\Service\WikiService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BaseController extends Controller
{

    /**
     * @return GitUserInterface|null
     */
    public function getUser()
    {
        return parent::getUser();
    }

    /**
     * @return WikiService
     */
    protected function getWikiService()
    {
        return $this->get('ddr.gitki.service.wiki');
    }

    protected function assertRole($role)
    {
        if (false === $this->get('security.context')->isGranted($role)) {
            throw new AccessDeniedException();
        }
    }

    protected function hasRole($role)
    {
        return $this->get('security.context')->isGranted($role);
    }

    protected function getEnvironment()
    {
        /** @var Kernel $kernel */
        $kernel = $this->container->get('kernel');

        return $kernel->getEnvironment();
    }
}
