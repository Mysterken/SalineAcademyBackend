<?php

namespace App\Tests\Command;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class AddUserRoleCommandTest extends KernelTestCase
{
    private Application $application;
    private ObjectManager $entityManager;

    public function testExecute()
    {
        $command = $this->application->find('app:add-user-role');
        $commandTester = new CommandTester($command);

        $randomRole = strtoupper(uniqid());

        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy([]);

        $commandTester->execute([
            'id' => $user->getId(),
            'role' => $randomRole,
        ]);

        $user = $userRepository->find($user->getId());
        $this->assertTrue(in_array("ROLE_$randomRole", $user->getRoles()));

        $commandTester->assertCommandIsSuccessful();
    }

    protected function setUp(): void
    {
        self::bootKernel(['environment' => 'test', 'debug' => false]);

        $this->application = new Application(self::$kernel);
        $this->entityManager = self::$kernel->getContainer()->get('doctrine')->getManager();
    }
}
