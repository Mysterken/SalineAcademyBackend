<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\Exception\NotSupported;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class RegisterUserTest extends AbstractApiControllerTest
{
    protected string $apiUrl = '/api/register';


    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws NotSupported
     */
    public function test(): void
    {
        $this->request('POST', $this->getApiUrl(), [
            'json' => [
                'username' => 'testRegisterUsername',
                'password' => 'testRegisterPassword',
                'email' => 'testRegisterEmail@email.com',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['message' => 'User successfully registered']);

        $user = $this->getEntityManager()->getRepository(User::class)->findOneBy([
            'email' => 'testRegisterEmail@email.com'
        ]);

        $this->assertNotNull($user);
        $this->assertSame('testRegisterUsername', $user->getUsername());
        $this->assertTrue($this->getUserPasswordHasher()->isPasswordValid($user, 'testRegisterPassword'));
    }
}
