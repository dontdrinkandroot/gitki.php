<?php


namespace Net\Dontdrinkandroot\Gitki\BaseBundle\Security;


use Symfony\Component\Security\Core\User\UserInterface;

interface User extends UserInterface
{

    public function getName();

    public function getEmail();

} 