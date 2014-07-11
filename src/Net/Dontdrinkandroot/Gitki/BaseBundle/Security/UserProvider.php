<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Security;


use Github\Client;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements OAuthAwareUserProviderInterface, UserProviderInterface
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


    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $fields = $response->getResponse();

        $user = new User();

        $id = $fields['id'];
        $user->setId($id);

        $login = $fields['login'];
        $user->setLogin($login);

        if (array_key_exists('name', $fields)) {
            $realName = $fields['name'];
            $user->setRealName($realName);
        }

        $client = new Client();
        $client->authenticate($response->getAccessToken(), Client::AUTH_HTTP_TOKEN);
        /* @var \Github\Api\CurrentUser $currentUserApi */
        $currentUserApi = $client->api('me');
        $emails = $currentUserApi->emails();
        $allEMails = $emails->all();

        // TODO: no distinction which is the primary yet, api does not support it?!
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
        return $class === 'Net\\Dontdrinkandroot\\Gitki\\BaseBundle\\Security\\User';
    }


}