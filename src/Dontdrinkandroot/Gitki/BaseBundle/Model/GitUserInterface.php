<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;

interface GitUserInterface extends UserInterface
{
    /**
     * @return string
     */
    public function getUsername();

    /**
     * @return string
     */
    public function getEmail();
}
