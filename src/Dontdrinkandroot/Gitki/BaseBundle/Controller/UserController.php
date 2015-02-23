<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

class UserController extends BaseController
{

    public function profileAction()
    {
        $user = $this->getUser();

        $connectedAcounts = [];
        if (!empty($user->getGithubId())) {
            $connectedAcounts['github'] = true;
        }
        if (!empty($user->getGoogleId())) {
            $connectedAcounts['google'] = true;
        }

        return $this->render(
            'DdrGitkiBaseBundle:User:profile.html.twig',
            ['user' => $user, 'connectedAcounts' => $connectedAcounts]
        );
    }

    /**
     * @return Response
     */
    public function loggedoutAction()
    {
        return $this->render('DdrGitkiBaseBundle:User:loggedout.html.twig');
    }
}