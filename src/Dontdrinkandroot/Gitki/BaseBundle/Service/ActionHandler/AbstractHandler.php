<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Service\ActionHandler;

use Dontdrinkandroot\Gitki\BaseBundle\Service\WikiService;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AbstractHandler extends ContainerAware
{

    /**
     * @return WikiService
     */
    protected function getWikiService()
    {
        return $this->container->get('ddr.gitki.service.wiki');
    }

    protected function redirect($url, $status = 302)
    {
        return new RedirectResponse($url, $status);
    }

    protected function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->container->get('router')->generate($route, $parameters, $referenceType);
    }

    protected function render($view, array $parameters = array(), Response $response = null)
    {
        return $this->container->get('templating')->renderResponse($view, $parameters, $response);
    }

    protected function assertRole($role)
    {
        if (false === $this->container->get('security.context')->isGranted($role)) {
            throw new AccessDeniedException();
        }
    }

    protected function createFormBuilder($data = null, array $options = array())
    {
        return $this->container->get('form.factory')->createBuilder('form', $data, $options);
    }
}
