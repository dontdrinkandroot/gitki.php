<?php

namespace App\Security\Voter;

use Dontdrinkandroot\Common\CrudOperation;
use Dontdrinkandroot\Path\DirectoryPath;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DirectoryPathVoter extends Voter
{
    public function __construct(private AuthorizationCheckerInterface $authorizationChecker)
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof DirectoryPath;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        return match ($attribute) {
            CrudOperation::READ => $this->authorizationChecker->isGranted('ROLE_WATCHER'),
            CrudOperation::CREATE, CrudOperation::DELETE, CrudOperation::UPDATE => $this->authorizationChecker->isGranted(
                'ROLE_COMMITTER'
            ),
            default => false
        };
    }
}
