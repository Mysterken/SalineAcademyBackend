<?php

namespace App\EventListener;

use App\Entity\Enrollment;
use Symfony\Bundle\SecurityBundle\Security;

readonly class EnrollmentListener
{
    public function __construct(private Security $security)
    {
    }

    public function prePersist(Enrollment $enrollment): void
    {
        $enrollment->setUser($this->security->getUser());
    }
}
