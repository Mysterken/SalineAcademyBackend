<?php

namespace App\EventListener;

use App\Entity\Rating;
use App\Repository\RatingRepository;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;

readonly class RatingListener
{
    public function __construct(private Security $security, private RatingRepository $ratingRepository)
    {
    }

    /**
     * @throws Exception
     */
    public function prePersist(Rating $rating): void
    {
        if ($this->ratingRepository->findOneBy([
            'author' => $this->security->getUser(),
            'masterclass' => $rating->getMasterclass()
        ])) {
            throw new Exception('You have already rated this masterclass');
        }

        if (
            $this->security->getUser() &&
            !$rating->getAuthor()
        ) {
            $rating->setAuthor($this->security->getUser());
        }
    }
}
