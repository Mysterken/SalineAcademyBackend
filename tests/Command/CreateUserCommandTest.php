<?php

namespace App\Tests\Command;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;

class CreateUserCommandTest extends KernelTestCase
{
    private Application $application;
    private ObjectManager $entityManager;
    private UserPasswordHasher $userPasswordHasher;

    /**
     * @throws Exception
     */
    public function testExecute(): void
    {
        $command = $this->application->find('app:create-user');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'username' => 'phpTestUsername',
            'password' => 'phpTestPassword',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Email: phpTestUsername@email.com', $output);
        $this->assertStringContainsString('Username: phpTestUsername', $output);
        $this->assertStringContainsString('Password: phpTestPassword', $output);

        preg_match('/Id: (\d+)/', $output, $matches);
        $id = $matches[1];

        $user = $this->entityManager->getRepository(User::class)->find($id);
        $this->assertNotNull($user);
        $this->assertSame('phpTestUsername', $user->getUsername());
        $this->assertSame('phpTestUsername@email.com', $user->getEmail());
        $this->assertTrue($this->userPasswordHasher->isPasswordValid($user, 'phpTestPassword'));

        $commandTester->assertCommandIsSuccessful();
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        self::bootKernel(['environment' => 'test', 'debug' => false]);

        $this->application = new Application(self::$kernel);
        $this->entityManager = self::$kernel->getContainer()->get('doctrine')->getManager();
        $this->userPasswordHasher = self::$kernel->getContainer()->get('test.password_hasher');
    }
}
