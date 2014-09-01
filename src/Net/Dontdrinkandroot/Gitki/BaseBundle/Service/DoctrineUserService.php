<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Service;

use Net\Dontdrinkandroot\Gitki\BaseBundle\Entity\User;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Repository\DoctrineUserRepository;
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
     * @param string $realName
     * @param string $email
     * @param array  $roles
     *
     * @return User
     */
    public function createUser($realName, $email, array $roles)
    {
        $user = new User();
        $user->setRealName($realName);
        $user->setEmail($email);
        $user->setRoles($roles);

        return $user;
    }

    /**
     * @param User   $user
     * @param string $newPassword
     *
     * @return User
     */
    public function changePassword(User $user, $newPassword)
    {
        $encoder = $this->encoderFactory->getEncoder($user);
        $salt = $this->secureRandom->nextBytes(16);
        $encodedPassword = $encoder->encodePassword($newPassword, $salt);
        $user->setSalt($salt);
        $user->setPassword($encodedPassword);

        return $user;
    }

    /**
     * @param \Net\Dontdrinkandroot\Gitki\BaseBundle\Entity\User $user
     *
     * @return mixed
     */
    public function saveUser(\Net\Dontdrinkandroot\Gitki\BaseBundle\Entity\User $user)
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
}