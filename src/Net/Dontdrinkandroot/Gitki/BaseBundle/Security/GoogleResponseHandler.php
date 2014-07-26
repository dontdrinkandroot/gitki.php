<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Security;


use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;

class GoogleResponseHandler implements ResponseHandler
{

    protected $adminUsers;
    protected $commitUsers;
    protected $watchUsers;

    public function __construct($adminUsers, $commitUsers, $watchUsers)
    {

        $this->adminUsers = array();
        $this->commitUsers = array();
        $this->watchUsers = array();

        if (null !== $adminUsers) {
            if (is_array($adminUsers)) {
                foreach ($adminUsers as $adminUser) {
                    $this->adminUsers[$adminUser] = true;
                }
            } else {
                $this->adminUsers[$adminUsers] = true;
            }
        }

        if (null !== $commitUsers) {
            if (is_array($commitUsers)) {
                foreach ($commitUsers as $commitUser) {
                    $this->commitUsers[$commitUser] = true;
                }
            } else {
                $this->commitUsers[$commitUsers] = true;
            }
        }

        if (null !== $watchUsers) {
            if (is_array($watchUsers)) {
                foreach ($watchUsers as $watchUser) {
                    $this->watchUsers[$watchUser] = true;
                }
            } else {
                $this->watchUsers[$watchUsers] = true;
            }
        }
    }

    public function handleResponse(UserResponseInterface $response)
    {
        $fields = $response->getResponse();

        $user = new GoogleUser();
        $user->setId($fields['id']);
        $user->setRealName($fields['name']);
        $user->setEMail($fields['email']);
        $user->setAccessToken($response->getAccessToken()); #

        $login = explode('@', $user->getEmail())[0];

        if (array_key_exists($login, $this->adminUsers)) {
            $user->addRole('ROLE_ADMIN');
        }

        if (array_key_exists($login, $this->commitUsers)) {
            $user->addRole('ROLE_COMMITER');
        }

        if (array_key_exists($login, $this->watchUsers)) {
            $user->addRole('ROLE_WATCHER');
        }

        return $user;
    }

    public function supportsClass($userClass)
    {
        return $userClass === 'Net\\Dontdrinkandroot\\Gitki\\BaseBundle\\Security\\GoogleUser';
    }

    public function handlesResourceOwner($resourceOwnerName)
    {
        return $resourceOwnerName === "google";
    }
} 