<?php

namespace App\Tests\Controller;

use App\Entity\Point;
use Exception;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AddPointControllerTest extends AbstractApiControllerTest
{
    protected string $apiUrl = '/api/points';


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

        $randomAmount = random_int(1, 100);

        $this->request('POST', $this->getApiUrl(), [
            'json' => [
                'amount' => $randomAmount,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['message' => "Added $randomAmount points to user"]);

        $points = $this->getEntityManager()->getRepository(Point::class)->findBy([], ['id' => 'DESC'], 1);
        $this->assertNotNull($points);
        $this->assertSame($randomAmount, $points[0]->getAmount());
    }
}
