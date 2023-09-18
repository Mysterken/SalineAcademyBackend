<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\EventListener\LessonListener;
use App\Repository\LessonRepository;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LessonRepository::class)]
#[ORM\EntityListeners([LessonListener::class])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(
            security: 'is_granted("PUBLIC_ACCESS")'
        ),
        new Put(
            denormalizationContext: ['groups' => ['lesson:update']],
            security: 'is_granted("ROLE_ADMIN") or (is_granted("ROLE_TEACHER") and object.getMasterclass().getAuthor() == user)'
        ),
        new Delete(
            security: 'is_granted("ROLE_ADMIN") or (is_granted("ROLE_TEACHER") and object.getMasterclass().getAuthor() == user)'
        ),
        new Patch(
            denormalizationContext: ['groups' => ['lesson:update']],
            security: 'is_granted("ROLE_ADMIN") or (is_granted("ROLE_TEACHER") and object.getMasterclass().getAuthor() == user)'
        ),
        new GetCollection(
            security: 'is_granted("PUBLIC_ACCESS")'
        ),
        new Post(
            denormalizationContext: ['groups' => ['lesson:write']],
            security: 'is_granted("ROLE_ADMIN") or is_granted("ROLE_TEACHER")'
        ),
    ],
    normalizationContext: ['groups' => ['lesson:read']],
    security: 'is_granted("ROLE_ADMIN")',
)]
class Lesson implements EntityTimestampInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['lesson:read', 'masterclass:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['lesson:read', 'lesson:write', 'lesson:update', 'masterclass:read'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['lesson:read', 'lesson:write', 'lesson:update', 'masterclass:read'])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Assert\Url(message: 'The url {{ value }} is not a valid url')]
    #[Groups(['lesson:read', 'lesson:write', 'lesson:update', 'masterclass:read'])]
    private ?string $videoUrl = null;

    #[ORM\Column]
    #[Assert\GreaterThan(0)]
    #[Groups(['lesson:read', 'lesson:write', 'lesson:update', 'masterclass:read'])]
    private ?int $masterclassOrder = null;

    #[ORM\ManyToOne(inversedBy: 'lessons')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['lesson:read', 'lesson:write'])]
    private ?Masterclass $masterclass = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'lesson', targetEntity: Task::class)]
    #[Groups(['lesson:read', 'lesson:write'])]
    private Collection $tasks;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }

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

    public function getVideoUrl(): ?string
    {
        return $this->videoUrl;
    }

    public function setVideoUrl(string $videoUrl): static
    {
        $this->videoUrl = $videoUrl;

        return $this;
    }

    public function getMasterclassOrder(): ?int
    {
        return $this->masterclassOrder;
    }

    public function setMasterclassOrder(int $masterclassOrder): static
    {
        $this->masterclassOrder = $masterclassOrder;

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

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): static
    {
        $this->createdAt = new DateTimeImmutable();

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setUpdatedAt(): static
    {
        $this->updatedAt = new DateTime();

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setLesson($this);
        }

        return $this;
    }

    public function removeTask(Task $task): static
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getLesson() === $this) {
                $task->setLesson(null);
            }
        }

        return $this;
    }
}
