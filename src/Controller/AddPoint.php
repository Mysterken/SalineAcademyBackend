<?php

namespace App\Controller;

use App\Entity\Point;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsController]
class AddPoint extends AbstractController
{
    public function __invoke(
        Request                $request,
        EntityManagerInterface $manager,
        ValidatorInterface     $validator,
        Security               $security,
    ): JsonResponse
    {
        $content = json_decode($request->getContent(), true);

        $amount = $content['amount'] ?? null;
        if (empty($amount) || !is_numeric($amount) || $amount < 0) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid amount',
            ], 400);
        }

        $points = new Point();
        $points
            ->setUser($security->getUser())
            ->setAmount($amount)
            ->setEarnedDate(new DateTimeImmutable());

        $errors = $validator->validate($points);
        if (count($errors) > 0) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid data',
            ], 400);
        }

        $manager->persist($points);
        $manager->flush();

        return $this->json([
            'status' => 'success',
            'message' => "Added $amount points to user",
        ]);
    }
}
