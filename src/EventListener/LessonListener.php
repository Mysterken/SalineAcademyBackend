<?php

namespace App\EventListener;

use App\Entity\Lesson;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;

readonly class LessonListener
{
    public function __construct(private Security $security)
    {
    }

    /**
     * @throws Exception
     */
    public function prePersist(Lesson $lesson): void
    {

        if ($lesson->getMasterclass()->getAuthor() !== $this->security->getUser()) {
            throw new Exception('You are not the author of this masterclass');
        }

        if (!$this->checkOrder($lesson)) {
            throw new Exception('This order is already taken');
        }
    }

    /**
     * Check if the masterclass order is not already taken
     */
    private function checkOrder(Lesson $lesson): bool
    {
        foreach ($lesson->getMasterclass()->getLessons() as $l) {
            if ($l->getMasterclassOrder() === $lesson->getMasterclassOrder()) {
                return false;
            }
        }
        return true;
    }

    /**
     * @throws Exception
     */
    public function preUpdate(Lesson $lesson): void
    {
        if ($lesson->getMasterclass()->getAuthor() !== $this->security->getUser()) {
            throw new Exception('You are not the author of this masterclass');
        }

        if (!$this->checkOrder($lesson)) {
            throw new Exception('This order is already taken');
        }
    }
}
