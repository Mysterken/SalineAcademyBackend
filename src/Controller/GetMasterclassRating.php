<?php

namespace App\Controller;

use App\Repository\MasterclassRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/api/masterclasses/{id}/rating", name: "homepage")]
class GetMasterclassRating extends AbstractController
{
    public function __construct(
        private readonly MasterclassRepository $masterclassRepository
    )
    {
    }

    public function __invoke(int $id): JsonResponse
    {
        $ratings = $this->masterclassRepository->find($id)->getRatings();
        $averageRating = 0;
        foreach ($ratings as $rating) {
            $averageRating += $rating->getValue();
        }
        $averageRating /= count($ratings);
        return new JsonResponse(["id" => $id, "averageRating" => $averageRating]);
    }
}
