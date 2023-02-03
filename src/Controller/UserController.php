<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\UserType;
use App\Repository\UserRepository;
use Dontdrinkandroot\Common\Asserted;
use Dontdrinkandroot\GitkiBundle\Controller\BaseController;
use Dontdrinkandroot\GitkiBundle\Service\Security\SecurityService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends BaseController
{
    public function __construct(
        SecurityService $securityService,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct($securityService);
    }

    public function listAction(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $this->userRepository->findBy([], ['email' => 'ASC']);

        return $this->render('User/list.html.twig', ['users' => $users]);
    }

    public function editAction(Request $request, int|string $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $newUser = 'new' === $id;
        $user = null;
        if (!$newUser) {
            $user = $this->userRepository->find($id);
            if (null === $user) {
                throw $this->createNotFoundException();
            }
            $newUser = false;
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = Asserted::instanceOf($form->getData(), User::class);
            $plainPassword = $form->get('plainPassword')->getData();
            if (null !== $plainPassword) {
                $user->password = $this->passwordHasher->hashPassword($user, $plainPassword);
            }

            if ($newUser) {
                $this->userRepository->create($user);
                return $this->redirectToRoute('ddr_gitki.user.edit', ['id' => $user->id]);
            }

            $this->userRepository->flush();
        }

        return $this->render('User/edit.html.twig', ['form' => $form->createView()]);
    }

    public function deleteAction(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $this->userRepository->find($id);
        if (null === $user) {
            throw $this->createNotFoundException();
        }

        $this->userRepository->remove($user);

        return $this->redirectToRoute('ddr_gitki.user.list');
    }
}
