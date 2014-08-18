<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Security;


use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Service\UserService;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class GoogleResponseHandler implements ResponseHandler
{

    public function handleResponse(UserResponseInterface $response, UserService $userService)
    {
        $fields = $response->getResponse();

        $email = $fields['email'];
        $login = explode('@', $email)[0];

        $user = $userService->findByGoogleLogin($login);
        if (null === $user) {
            throw new UsernameNotFoundException();
        }

        $oAuthUser = new OAuthUser($user);
        $oAuthUser->addRole('ROLE_GOOGLE_USER');
        $oAuthUser->setAccessToken($response->getAccessToken());
        $oAuthUser->setRealName($fields['name']);
        $oAuthUser->setPrimaryEmailAddress($email);
        $oAuthUser->setEmailAddresses([$email]);

        return $oAuthUser;
    }

    public function supportsClass($userClass)
    {
        return $userClass === 'Net\\Dontdrinkandroot\\Gitki\\BaseBundle\\Security\\OAuthUser';
    }

    public function handlesResourceOwner($resourceOwnerName)
    {
        return $resourceOwnerName === "google";
    }
} 