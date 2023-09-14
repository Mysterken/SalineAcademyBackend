<?php

namespace App\EventListener;

use App\Entity\Task;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;

readonly class TaskListener
{
    public function __construct(private Security $security)
    {
    }

    /**
     * @throws Exception
     */
    public function prePersist(Task $task): void
    {
        if (
            $this->security->getUser() &&
            $task->getLesson()->getMasterclass()->getAuthor() !== $this->security->getUser()
        ) {
            throw new Exception('You are not the author of this masterclass');
        }
    }
}
