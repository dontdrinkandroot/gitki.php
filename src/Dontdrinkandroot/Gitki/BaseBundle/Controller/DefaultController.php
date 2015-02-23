<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class DefaultController extends BaseController
{
    public function indexAction()
    {
        return $this->redirect($this->generateUrl('ddr_gitki_wiki_directory', ['path' => '/', 'action' => 'index']));
    }

    public function loginAction(Request $request)
    {
        if (!$this->hasRole('ROLE_USER')) {
            if ($request->hasSession() && $request->isMethodSafe()) {
                $referer = $request->headers->get('referer');
                if (null !== $referer) {
                    $request->getSession()->set('ddr.gitki.manuallogin.targetpath', $referer);
                }
            }
            throw new AuthenticationException();
        }

        if ($request->hasSession() && $request->isMethodSafe()) {
            $targetPath = $request->getSession()->get('ddr.gitki.manuallogin.targetpath');
            $request->getSession()->remove('ddr.gitki.manuallogin.targetpath');
            if (null !== $targetPath) {
                return $this->redirect($targetPath);
            }
        }

        return $this->redirect($this->generateUrl('ddr_gitki_wiki_file', array('path' => '/index.md')));
    }
}
