<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Security;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Service\UserService;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements OAuthAwareUserProviderInterface, UserProviderInterface
{

    /**
     * @var ResponseHandler[]
     */
    protected $responseHandlers = array();

    /**
     * @var UserService
     */
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function registerHandler(ResponseHandler $handler)
    {
        $this->responseHandlers[] = $handler;
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $resourceOwnerName = $response->getResourceOwner()->getName();
        foreach ($this->responseHandlers as $responseHandler) {
            if ($responseHandler->handlesResourceOwner($resourceOwnerName)) {
                return $responseHandler->handleResponse($response, $this->userService);
            }
        }

        throw new \Exception("Unsupported oauth type: " . $resourceOwnerName);
    }

    public function loadUserByUsername($username)
    {
        $user = $this->userService->findByEmail($username);
        if (null === $user) {
            throw new UsernameNotFoundException();
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', get_class($user)));
        }

        return $user;
    }

    public function supportsClass($class)
    {
        return is_subclass_of($class, 'Net\Dontdrinkandroot\Gitki\BaseBundle\Security\User');

//        foreach ($this->responseHandlers as $responseHandler) {
//            if ($responseHandler->supportsClass($class)) {
//                return true;
//            }
//        }
//
//        return false;
    }
}