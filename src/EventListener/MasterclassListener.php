<?php

namespace App\EventListener;

use App\Entity\Masterclass;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;

readonly class MasterclassListener
{
    public function __construct(private Security $security)
    {
    }

    public function prePersist(Masterclass $masterclass): void
    {
        $masterclass->setAuthor($this->security->getUser());
    }

    /**
     * @throws Exception
     */
    public function preUpdate(Masterclass $masterclass): void
    {
        if ($masterclass->getAuthor() !== $this->security->getUser()) {
            throw new Exception('You are not the author of this masterclass');
        }
    }
}
