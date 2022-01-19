<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserAdminCheckCommand extends Command
{
    private const ADMIN_EMAIL = 'admin@example.com';

    protected static $defaultName = 'app:user:admin-check';

    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Checks if a user with email "admin@example.com" is available or creates it');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = $this->userRepository->findOneBy(['email' => self::ADMIN_EMAIL]);
        if (null !== $user) {
            $output->writeln('Admin user already exists');

            return 0;
        }

        $password = bin2hex(random_bytes(16));
        $user = new User(
            email: self::ADMIN_EMAIL,
            realName: 'Administration User',
            roles: ['ROLE_ADMIN']
        );
        $user->password = $this->passwordHasher->hashPassword($user, $password);
        $this->userRepository->create($user);

        $output->writeln('Create User: ' . self::ADMIN_EMAIL);
        $output->writeln('Password: ' . $password);

        return 0;
    }
}
