<?php

namespace Dontdrinkandroot\Gitki\WebBundle\Controller;

use Dontdrinkandroot\Gitki\WebBundle\Entity\User;
use Dontdrinkandroot\Gitki\WebBundle\Form\UserEditType;
use Dontdrinkandroot\GitkiBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class UserController extends BaseController
{
    public function listAction()
    {
        $this->assertAdmin();
        $users = $this->get('fos_user.user_manager')->findUsers();

        return $this->render('@DdrGitkiWeb/User/list.html.twig', ['users' => $users]);
    }

    public function editAction(Request $request, $id)
    {
        $this->assertAdmin();

        $userManager = $this->get('fos_user.user_manager');
        $newUser = false;
        if ('new' === $id) {
            $newUser = true;
            $user = $userManager->createUser();
        } else {
            $userRepository = $this->get('doctrine')->getRepository(User::class);
            $user = $userRepository->find($id);
            if (null === $user) {
                throw $this->createNotFoundException();
            }
        }

        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->updateUser($form->getData());

            if ($newUser) {
                return $this->redirectToRoute('ddr_gitki.user.edit', ['id' => $user->getId()]);
            }
        }

        return $this->render('@DdrGitkiWeb/User/edit.html.twig', ['form' => $form->createView()]);
    }

    public function deleteAction($id)
    {
        $this->assertAdmin();

        $userRepository = $this->get('doctrine')->getRepository(User::class);
        /** @var User $user */
        $user = $userRepository->find($id);
        if (null === $user) {
            throw $this->createNotFoundException();
        }

        $userManager = $this->get('fos_user.user_manager');
        $userManager->deleteUser($user);

        return $this->redirectToRoute('ddr_gitki.user.list');
    }
}
