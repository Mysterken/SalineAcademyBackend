<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Creates a new user.',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PasswordHasherFactoryInterface $passwordHasher,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'The username of the user.')
            ->addArgument('password', InputArgument::OPTIONAL, 'The password of the user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            '<question>User Creator',
            '============',
            '</question>',
        ]);

        $email = $input->getArgument('username') . '@email.com';
        $username = $input->getArgument('username');
        $password = $input->getArgument('password') ?? $input->getArgument('username');

        $passwordHasher = $this->passwordHasher->getPasswordHasher(User::class);

        $user = new User();
        $user
            ->setEmail($email)
            ->setUsername($username)
            ->setPassword($passwordHasher->hash($password))
            ->setRoles(['ROLE_USER']);

        if ($username === 'admin') {
            $user->addRole('ROLE_ADMIN');
        } elseif ($username === 'teacher') {
            $user->addRole('ROLE_TEACHER');
        }

        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } catch (Exception $e) {
            $output->writeln('<error>Error: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln([
            '<info>User successfully generated!</info>',
            '',
            "Id: " . $user->getId(),
            "Email: $email",
            "Username: $username",
            "Password: $password",
        ]);

        return Command::SUCCESS;
    }
}
