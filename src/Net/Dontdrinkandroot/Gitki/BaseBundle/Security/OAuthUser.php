<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Security;

use Net\Dontdrinkandroot\Gitki\BaseBundle\Entity\User;

class OAuthUser extends User
{

    private $roles = ['ROLE_OAUTH_USER'];

    private $accessToken;

    public function __construct()
    {
    }
}