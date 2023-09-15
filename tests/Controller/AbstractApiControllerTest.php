<?php

namespace App\Tests\Controller;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

abstract class AbstractApiControllerTest extends ApiTestCase
{
    protected string $apiUrl;
    private Client $client;
    private EntityManager $entityManager;
    private UserPasswordHasher $userPasswordHasher;
    private User $testUser;

    abstract public function test(): void;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = self::$kernel->getContainer()->get('doctrine')->getManager();
        $this->userPasswordHasher = self::$kernel->getContainer()->get('test.password_hasher');
    }

    protected function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    protected function getApiUrlWithId(int $id): string
    {
        return str_replace('{id}', $id, $this->getApiUrl());
    }

    /**
     * @throws Exception
     * @throws TransportExceptionInterface
     * @throws DecodingExceptionInterface
     */
    protected function request(string $type, string $url, array $data = []): array
    {
        if (!in_array($type, ['GET', 'POST', 'PUT', 'DELETE'])) {
            throw new Exception('Invalid request type');
        }

        $response = $this->getClient()->request($type, $url, $data);

        $this->assertResponseIsSuccessful();

        return $response->toArray();
    }

    protected function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @throws Exception
     */
    protected function loginClient(): void
    {
        $this->getClient()->loginUser($this->getTestUser());
    }

    protected function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    protected function getUserPasswordHasher(): UserPasswordHasher
    {
        return $this->userPasswordHasher;
    }

    /**
     * @throws Exception
     */
    protected function getTestUser(): User
    {
        if (!isset($this->testUser)) {
            $this->setTestUser();
        }

        return $this->testUser;
    }

    /**
     * @throws Exception
     */
    private function setTestUser(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneBy(['email' => 'test@email.com']);
        if (!$testUser) {
            throw new Exception('Test user not found');
        }

        $this->testUser = $testUser;
    }

}
