<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\UpdateProgress;
use App\EventListener\ProgressListener;
use App\Repository\ProgressRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProgressRepository::class)]
#[ORM\EntityListeners([ProgressListener::class])]
#[ApiResource(
    operations: [
        new Get(),
        new Put(
            controller: UpdateProgress::class,
            denormalizationContext: ['groups' => ['progress:update']],
        ),
        new Delete(),
        new Patch(
            controller: UpdateProgress::class,
            denormalizationContext: ['groups' => ['progress:update']],
        ),
        new GetCollection(
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Post(
            denormalizationContext: ['groups' => ['progress:write']],
            security: 'is_granted("ROLE_ADMIN") or is_granted("ROLE_USER")',
        ),
    ],
    normalizationContext: ['groups' => ['progress:read']],
    security: 'is_granted("ROLE_ADMIN") or (is_granted("ROLE_USER") and object.getUser() === user)',
)]
class Progress
{
    const COMPLETION_STATUS_IN_PROGRESS = 1;
    const COMPLETION_STATUS_COMPLETED = 2;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['progress:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'progress')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['progress:read'])]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: 'false')]
    #[Groups(['progress:read', 'progress:write'])]
    private ?Lesson $lesson = null;

    #[ORM\Column]
    #[Groups(['progress:read'])]
    private ?int $points = 10;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['progress:read'])]
    private ?DateTimeInterface $completionDate = null;

    #[Assert\Choice(callback: 'getCompletionStatusList', message: 'Invalid completion status')]
    #[Groups(['progress:read', 'progress:write', 'progress:update'])]
    private ?int $completionStatus = self::COMPLETION_STATUS_IN_PROGRESS;

    public static function getCompletionStatusList(): array
    {
        return [
            self::COMPLETION_STATUS_IN_PROGRESS,
            self::COMPLETION_STATUS_COMPLETED,
        ];
    }

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

    public function getLesson(): ?Lesson
    {
        return $this->lesson;
    }

    public function setLesson(?Lesson $lesson): static
    {
        $this->lesson = $lesson;

        return $this;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(int $points): static
    {
        $this->points = $points;

        return $this;
    }

    public function getCompletionDate(): ?DateTimeInterface
    {
        return $this->completionDate;
    }

    public function setCompletionDate(?DateTimeInterface $completionDate): static
    {
        $this->completionDate = $completionDate;

        return $this;
    }

    public function getCompletionStatus(): ?int
    {
        if ($this->completionStatus === null) {
            $this->setCompletionStatus(self::COMPLETION_STATUS_IN_PROGRESS);
        }
        return $this->completionStatus;
    }

    public function setCompletionStatus(?int $completionStatus): Progress
    {
        $this->completionStatus = $completionStatus;
        return $this;
    }

}
