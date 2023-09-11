<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\EventListener\EnrollmentListener;
use App\Repository\EnrollmentRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EnrollmentRepository::class)]
#[ORM\EntityListeners([EnrollmentListener::class])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(),
        new Put(),
        new Delete(
            security: 'is_granted("ROLE_ADMIN") or (is_granted("ROLE_USER") and object.getUser() == user)',
        ),
        new Patch(),
        new GetCollection(),
        new Post(
            denormalizationContext: ['groups' => ['enrollment:write']],
            security: 'is_granted("ROLE_USER")',
        ),
    ],
    security: 'is_granted("ROLE_ADMIN")'
)]
class Enrollment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'enrollments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?DateTimeImmutable $enrollmentDate = null;

    #[ORM\ManyToOne(inversedBy: 'enrollments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['enrollment:write'])]
    private ?Masterclass $masterclass = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getEnrollmentDate(): ?DateTimeImmutable
    {
        return $this->enrollmentDate;
    }

    #[ORM\PrePersist]
    public function setEnrollmentDate(): static
    {
        $this->enrollmentDate = new DateTimeImmutable();

        return $this;
    }

    public function getMasterclass(): ?Masterclass
    {
        return $this->masterclass;
    }

    public function setMasterclass(?Masterclass $masterclass): static
    {
        $this->masterclass = $masterclass;

        return $this;
    }
}
