<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Security;


use Github\Client;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;

class GitHubResponseHandler implements ResponseHandler
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

        $user = new GitHubUser();

        $id = $fields['id'];
        $user->setId($id);

        $login = $fields['login'];
        $user->setLogin($login);

        $accessToken = $response->getAccessToken();
        $user->setAccessToken($accessToken);

        if (array_key_exists('name', $fields)) {
            $realName = $fields['name'];
            $user->setRealName($realName);
        }

        $client = new Client();
        $client->setOption('api_version', 'v3');
        $client->authenticate($response->getAccessToken(), Client::AUTH_HTTP_TOKEN);
        /* @var \Github\Api\CurrentUser $currentUserApi */
        $currentUserApi = $client->api('current_user');
        $emails = $currentUserApi->emails();
        $allEMails = $emails->all();

        $user->setEMails($allEMails);

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
        return $userClass === 'Net\\Dontdrinkandroot\\Gitki\\BaseBundle\\Security\\GitHubUser';
    }

    public function handlesResourceOwner($resourceOwnerName)
    {
        return $resourceOwnerName === "github";
    }
} 