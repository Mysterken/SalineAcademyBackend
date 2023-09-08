<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:add-user-role',
    description: 'Adds a role to a user.',
)]
class AddUserRole extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED, 'The id of the user.')
            ->addArgument('role', InputArgument::REQUIRED, 'The role to add to the user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            '<question>User Role Adder',
            '============',
            '</question>',
        ]);

        $id = $input->getArgument('id');
        $role = "ROLE_" . strtoupper($input->getArgument('role'));

        if (!$user = $this->entityManager->getRepository(User::class)->find($id)) {
            $output->writeln([
                '<error>User not found.</error>',
            ]);

            return Command::FAILURE;
        }

        $user->addRole($role);

        $this->entityManager->flush();

        $output->writeln([
            "<info>Role '$role' added to " . $user->getUsername() . ".</info>",
        ]);

        return Command::SUCCESS;
    }
}
