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
     * @param string $email
     *
     * @return User|null
     */
    public function findByEmail($email)
    {
        $users = $this->userRepository->findBy(['email' => $email]);
        if (empty($users)) {
            return null;
        }

        if (count($users) > 1) {
            throw new \Exception('Found more than one user for email \"' . $email . '\"');
        }

        return $users[0];
    }

    /**
     * @param string $login
     *
     * @return User|null
     */
    public function findByGitHubLogin($login)
    {
        $users = $this->userRepository->findBy(['githubLogin' => $login]);
        if (empty($users)) {
            return null;
        }

        if (count($users) > 1) {
            throw new \Exception('Found more than one user for github login \"' . $login . '\"');
        }

        return $users[0];
    }

    /**
     * @param string $login
     *
     * @return User|null
     */
    public function findByGoogleLogin($login)
    {
        $users = $this->userRepository->findBy(['googleLogin' => $login]);
        if (empty($users)) {
            return null;
        }

        if (count($users) > 1) {
            throw new \Exception('Found more than one user for google login \"' . $login . '\"');
        }

        return $users[0];
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
