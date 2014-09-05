<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Security;

use Github\Client;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Net\Dontdrinkandroot\Gitki\BaseBundle\Service\UserService;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class GitHubResponseHandler implements ResponseHandler
{

    public function handleResponse(UserResponseInterface $response, UserService $userService)
    {
        $fields = $response->getResponse();

        $gitHubLogin = $fields['login'];
        $accessToken = $response->getAccessToken();

        $user = $userService->findByGitHubLogin($gitHubLogin);
        if (null === $user) {
            throw new UsernameNotFoundException();
        }

        $oAuthUser = new OAuthUser($user);
        $oAuthUser->addRole('ROLE_GITHUB_USER');
        $oAuthUser->setAccessToken($accessToken);

        if (array_key_exists('name', $fields)) {
            $gitHubName = $fields['name'];
            $oAuthUser->setRealName($gitHubName);
        } else {
            $oAuthUser->setRealName($gitHubLogin);
        }

        $client = new Client();
        $client->setOption('api_version', 'v3');
        $client->authenticate($response->getAccessToken(), Client::AUTH_HTTP_TOKEN);
        /* @var \Github\Api\CurrentUser $currentUserApi */
        $currentUserApi = $client->api('current_user');
        $emails = $currentUserApi->emails();
        $allEMails = $emails->all();

        $oAuthUser->setEmail($this->getPrimaryEmailAddress($allEMails));

        return $oAuthUser;
    }

    public function handlesResourceOwner($resourceOwnerName)
    {
        return $resourceOwnerName === "github";
    }

    protected function getEmailAddresses($emails)
    {
        $addresses = [];
        foreach ($emails as $eMail) {
            $addresses[] = $eMail['email'];
        }

        return $addresses;
    }

    protected function getPrimaryEmailAddress($emails)
    {
        $primaryEmail = null;
        foreach ($emails as $eMail) {
            if ($eMail['primary']) {
                $primaryEmail = $eMail['email'];
            }
        }

        if (null === $primaryEmail) {
            throw new \Exception('No primary eMail address found');
        }

        return $primaryEmail;
    }
} 