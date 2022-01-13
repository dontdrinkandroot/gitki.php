<?php

namespace App\Controller;

use Dontdrinkandroot\GitkiBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class DefaultController extends BaseController
{
    public function indexAction(): Response
    {
        return $this->redirect($this->generateUrl('ddr_gitki_directory', ['path' => '/', 'action' => 'index']));
    }

    public function loginAction(Request $request): Response
    {
        if (!$this->isGranted('ROLE_USER')) {
            /* Save targetpath if user was not logged in yet before redirecting to login page */
            if ($request->hasSession() && $request->isMethodSafe()) {
                $referer = $request->headers->get('referer');
                if (null !== $referer) {
                    $request->getSession()->set('ddr.gitki.manuallogin.targetpath', $referer);
                }
            }

            throw new AuthenticationException();
        }

        if ($request->hasSession() && $request->isMethodSafe()) {
            /* Restore target path after login */
            $targetPath = $request->getSession()->get('ddr.gitki.manuallogin.targetpath');
            $request->getSession()->remove('ddr.gitki.manuallogin.targetpath');
            if (null !== $targetPath) {
                return $this->redirect($targetPath);
            }
        }

        return $this->redirect($this->generateUrl('ddr_gitki_directory', ['path' => '/', 'action' => 'index']));
    }

    public function loggedoutAction(): Response
    {
        return $this->render('Default/loggedout.html.twig');
    }
}
