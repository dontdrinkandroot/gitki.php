<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Orm\Entity(repositoryClass: UserRepository::class)]
#[Orm\Table("`User`")]
class User implements UserInterface, PasswordAuthenticatedUserInterface, GitUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: Types::BIGINT, nullable: false)]
    #[ORM\GeneratedValue]
    public int $id;

    public function __construct(
        #[Assert\Email]
        #[Assert\NotBlank]
        #[ORM\Column(type: Types::STRING, nullable: false, unique: true)]
        public string $email,
        #[Assert\NotBlank]
        #[ORM\Column(type: Types::STRING, nullable: false)]
        public string $realName,
        #[ORM\Column(type: Types::ARRAY, nullable: false)]
        public array $roles = ['ROLE_USER'],
        #[ORM\Column(type: Types::STRING, nullable: false)]
        public string $password = ''
    ) {
    }

    public function getGitUserName(): string
    {
        return $this->realName;
    }

    public function getGitUserEmail(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
        /* Noop */
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function getUserIdentifier(): string
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
