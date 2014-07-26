<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Security;


use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements OAuthAwareUserProviderInterface, UserProviderInterface
{

    /**
     * @var ResponseHandler[]
     */
    protected $responseHandlers = array();

    public function __construct()
    {
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
                return $responseHandler->handleResponse($response);
            }
        }

        throw new \Exception("Unsupported oauth type: " . $resourceOwnerName);
    }

    public function loadUserByUsername($username)
    {
        throw new \Exception('Not possible to load user by name');
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
        foreach ($this->responseHandlers as $responseHandler) {
            if ($responseHandler->supportsClass($class)) {
                return true;
            }
        }

        return false;
    }


}