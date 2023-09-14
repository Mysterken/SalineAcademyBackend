<?php

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class CreateUserCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:create-user');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'username' => 'phpTestUsername',
            'password' => 'phpTestPassword',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Username: phpTestUsername', $output);
        $this->assertStringContainsString('Password: phpTestPassword', $output);

        $commandTester->assertCommandIsSuccessful();
    }
}
