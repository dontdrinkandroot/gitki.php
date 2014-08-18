<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Service;


use Net\Dontdrinkandroot\Gitki\BaseBundle\Model\VolatileUser;

class VolatileUserService implements UserService
{

    protected $usersById;

    protected $usersByGithubLogin;

    protected $usersByGoogleLogin;

    public function __construct(array $users)
    {
        $this->usersById = [];
        $this->usersByGithubLogin = [];
        $this->usersByGoogleLogin = [];
        foreach ($users as $rawUser) {
            $user = $this->parseUser($rawUser);
            $this->usersById[$user->getId()] = $user;
            if (isset($rawUser['github_login'])) {
                $this->usersByGithubLogin[$rawUser['github_login']] = $user;
            }
            if (isset($rawUser['google_login'])) {
                $this->usersByGoogleLogin[$rawUser['google_login']] = $user;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function findByGitHubLogin($login)
    {
        if (isset($this->usersByGithubLogin[$login])) {
            return $this->usersByGithubLogin[$login];
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function findByGoogleLogin($login)
    {
        if (isset($this->usersByGoogleLogin[$login])) {
            return $this->usersByGoogleLogin[$login];
        }

        return null;
    }

    private function parseUser($rawUser)
    {
        $user = new VolatileUser($rawUser['id']);
        if (isset($rawUser['real_name'])) {
            $user->setRealName($rawUser['real_name']);
        }
        if (isset($rawUser['primary_email_address'])) {
            $user->setPrimaryEmailAddress($rawUser['primary_email_address']);
        }
        if (isset($rawUser['roles'])) {
            foreach ($rawUser['roles'] as $role) {
                $user->addRole($role);
            }
        }

        return $user;
    }


}