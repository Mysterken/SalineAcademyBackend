<?php

namespace App\Controller;

use App\Entity\Progress;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UpdateProgress extends AbstractController
{
    public function __invoke(
        Progress               $progress,
        Security               $security,
        Request                $request,
        EntityManagerInterface $manager,
    ): JsonResponse
    {

        $content = json_decode($request->getContent(), true);

        if (!isset($content["completionStatus"])) {
            return $this->json([
                'status' => 'error',
                'message' => "Missing required field 'completionStatus'",
            ], 400);
        } elseif (!in_array($content["completionStatus"], Progress::getCompletionStatusList())) {
            return $this->json([
                'status' => 'error',
                'message' => "Invalid completionStatus",
            ], 400);
        }

        if ($content["completionStatus"] === Progress::COMPLETION_STATUS_IN_PROGRESS) {
            $progress->setCompletionDate(null);
            $message = "Progress updated to in progress";
        } elseif ($content["completionStatus"] === Progress::COMPLETION_STATUS_COMPLETED) {
            $progress->setCompletionDate(new DateTime());
            $message = "Progress updated to completed";
        } else {
            return $this->json([
                'status' => 'error',
                'message' => "Invalid completionStatus",
            ], 400);
        }

        $manager->persist($progress);
        $manager->flush();

        return $this->json([
            'status' => 'success',
            'message' => $message,
        ]);
    }
}
