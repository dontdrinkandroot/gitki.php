<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Service;

use Dontdrinkandroot\Gitki\BaseBundle\Entity\User;
use Dontdrinkandroot\Gitki\BaseBundle\Repository\DoctrineUserRepository;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Util\SecureRandom;

class DoctrineUserService implements UserService
{

    /**
     * @var DoctrineUserRepository
     */
    protected $userRepository;

    /**
     * @var SecureRandom
     */
    protected $secureRandom;

    /**
     * @var EncoderFactory
     */
    protected $encoderFactory;

    public function __construct(
        DoctrineUserRepository $userRepository,
        EncoderFactory $encoderFactory,
        SecureRandom $secureRandom
    ) {
        $this->userRepository = $userRepository;
        $this->encoderFactory = $encoderFactory;
        $this->secureRandom = $secureRandom;
    }

    /**
     * @return User[]
     */
    public function listUsers()
    {
        return $this->userRepository->findAll();
    }

    /**
     * @param User $user
     *
     * @return mixed
     */
    public function saveUser(User $user)
    {
        $this->userRepository->save($user);
    }

    /**
     * @param int $id
     *
     * @return User|null
     */
    public function findUserById($id)
    {
        return $this->userRepository->findOneBy(['id' => $id]);
    }

    /**
     * @param User $user
     *
     * @return mixed
     */
    public function deleteUser(User $user)
    {
        $this->userRepository->remove($user);
    }
}
