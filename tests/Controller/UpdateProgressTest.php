<?php

namespace App\Tests\Controller;

use App\Entity\Lesson;
use App\Entity\Progress;
use Exception;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class UpdateProgressTest extends AbstractApiControllerTest
{
    protected string $apiUrl = '/api/progress/{id}';

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

        // Create new progress with a random lesson
        $lesson = $this->getEntityManager()->getRepository(Lesson::class)->findOneBy([], ['id' => 'DESC']);

        $progress = new Progress();
        $progress
            ->setUser($this->getTestUser())
            ->setLesson($lesson);

        $this->getEntityManager()->persist($progress);
        $this->getEntityManager()->flush();

        // try to update the progress
        $this->request('PUT', $this->getApiUrlWithId($progress->getId()), [
            'json' => [
                'completionStatus' => Progress::COMPLETION_STATUS_COMPLETED,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['message' => 'Progress updated to completed']);

        $this->getEntityManager()->refresh($progress);

        $this->assertSame(Progress::COMPLETION_STATUS_COMPLETED, $progress->getCompletionStatus());
        $this->assertNotNull($progress->getCompletionDate());
    }
}
