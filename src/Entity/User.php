<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[Orm\Entity(repositoryClass: UserRepository::class)]
#[Orm\Table("`User`")]
class User implements UserInterface, PasswordAuthenticatedUserInterface, GitUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: Types::BIGINT, nullable: false)]
    #[ORM\GeneratedValue]
    public int $id;

    public function __construct(
        #[ORM\Column(type: Types::STRING, nullable: false)]
        public string $email,
        #[ORM\Column(type: Types::STRING, nullable: false)]
        public string $realName,
        #[ORM\Column(type: Types::ARRAY, nullable: false)]
        public array $roles = ['ROLE_USER'],
        #[ORM\Column(type: Types::STRING, nullable: false)]
        public string $password = ''
    ) {
    }

    public function getGitUserName()
    {
        return $this->realName;
    }

    public function getGitUserEmail()
    {
        return $this->email;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
        /* Noop */
    }

    public function getUsername()
    {
        return $this->getUserIdentifier();
    }

    public function getUserIdentifier()
    {
        return $this->email;
    }

    public function __toString()
    {
        $s = 'id=' . $this->id;
        $s .= ',realName=' . $this->realName;
        $s .= ',email=' . $this->email;

        return $s;
    }
}
