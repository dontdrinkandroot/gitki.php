<?php

namespace App\Security\Voter;

use Dontdrinkandroot\GitkiBundle\Security\GitkiVoter as BaseGitkiVoter;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class GitkiVoter extends BaseGitkiVoter
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly bool $publicRead
    ) {
    }

    protected function voteOnReadHistory(mixed $subject, TokenInterface $token): bool
    {
        if (null === $subject || $subject instanceof FilePath) {
            if ($this->publicRead) {
                return true;
            }

            return $this->authorizationChecker->isGranted('ROLE_WATCHER');
        }

        return false;
    }

    protected function voteOnWritePath(mixed $subject, TokenInterface $token): bool
    {
        if ($subject instanceof DirectoryPath || $subject instanceof FilePath) {
            return $this->authorizationChecker->isGranted('ROLE_COMMITTER');
        }

        return false;
    }

    protected function voteOnReadPath(mixed $subject, TokenInterface $token): bool
    {
        if (null === $subject || $subject instanceof DirectoryPath || $subject instanceof FilePath) {
            if ($this->publicRead) {
                return true;
            }

            return $this->authorizationChecker->isGranted('ROLE_WATCHER');
        }

        return false;
    }
}
