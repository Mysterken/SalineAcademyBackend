<?php

namespace App\EventListener;

use App\Entity\Progress;
use App\Repository\ProgressRepository;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;

readonly class ProgressListener
{
    public function __construct(private Security $security, private ProgressRepository $progressRepository)
    {
    }

    /**
     * @throws Exception
     */
    public function prePersist(Progress $progress): void
    {
        if ($this->progressRepository->findOneBy([
            'user' => $this->security->getUser(),
            'lesson' => $progress->getLesson()
        ])) {
            throw new Exception('You have already started this lesson');
        }

        if (
            $this->security->getUser() &&
            !$progress->getUser()
        ) {
            $progress->setUser($this->security->getUser());
        }
    }
}
