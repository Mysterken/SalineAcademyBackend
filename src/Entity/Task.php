<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\EventListener\TaskListener;
use App\Repository\TaskRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\EntityListeners([TaskListener::class])]
#[ApiResource(
    operations: [
        new Get(
            security: 'is_granted("PUBLIC_ACCESS")',
        ),
        new Put(
            denormalizationContext: ['groups' => ['task:update']],
            security: 'is_granted("ROLE_ADMIN") or (is_granted("ROLE_TEACHER") and object.getLesson().getMasterclass().getAuthor() == user)',
        ),
        new Delete(
            security: 'is_granted("ROLE_ADMIN") or (is_granted("ROLE_TEACHER") and object.getLesson().getMasterclass().getAuthor() == user)',
        ),
        new Patch(
            denormalizationContext: ['groups' => ['task:update']],
            security: 'is_granted("ROLE_ADMIN") or (is_granted("ROLE_TEACHER") and object.getLesson().getMasterclass().getAuthor() == user)',
        ),
        new GetCollection(),
        new Post(
            denormalizationContext: ['groups' => ['task:write']],
            security: 'is_granted("ROLE_ADMIN") or is_granted("ROLE_TEACHER")',
        ),
    ],
    normalizationContext: ['groups' => ['task:read']],
    security: 'is_granted("ROLE_ADMIN")',
)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['task:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['task:read', 'task:write', 'task:update'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['task:read', 'task:write', 'task:update'])]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['task:read', 'task:write'])]
    private ?Lesson $lesson = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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
}
