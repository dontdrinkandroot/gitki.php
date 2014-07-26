<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Security;


use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;

interface ResponseHandler
{
    public function handleResponse(UserResponseInterface $response);

    public function handlesResourceOwner($resourceOwnerName);

    public function supportsClass($userClass);
} 