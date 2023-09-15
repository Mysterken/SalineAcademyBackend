<?php

namespace App\Tests\Controller;

use Exception;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class UpdateUserPasswordTest extends AbstractApiControllerTest
{
    protected string $apiUrl = '/api/users/{id}/password';

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    public function test(): void
    {
        $this->loginClient();

        $this->request('POST', $this->getApiUrlWithId($this->getTestUser()->getId()), [
            'json' => [
                'oldPassword' => self::TEST_PASSWORD,
                'newPassword' => 'newPassword',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['message' => 'Password successfully updated']);

        $response = $this->request('POST', '/api/login_check', [
            'json' => [
                'username' => self::TEST_EMAIL,
                'password' => 'newPassword',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertNotNull($response['token']);
    }
}
