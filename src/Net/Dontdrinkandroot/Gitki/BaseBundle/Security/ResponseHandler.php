<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Security;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Service\UserService;

interface ResponseHandler
{
    public function handleResponse(UserResponseInterface $response, UserService $userService);

    public function handlesResourceOwner($resourceOwnerName);
}