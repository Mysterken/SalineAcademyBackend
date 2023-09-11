<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\EventListener\MasterclassListener;
use App\Repository\MasterclassRepository;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MasterclassRepository::class)]
#[ORM\EntityListeners([MasterclassListener::class])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(
            security: 'is_granted("PUBLIC_ACCESS")',
        ),
        new Put(
            denormalizationContext: ['groups' => ['masterclass:write']],
            security: 'is_granted("ROLE_ADMIN") or (is_granted("ROLE_TEACHER") and object.getAuthor() == user)',
        ),
        new Delete(
            security: 'is_granted("ROLE_ADMIN") or (is_granted("ROLE_TEACHER") and object.getAuthor() == user)',
        ),
        new Patch(
            denormalizationContext: ['groups' => ['masterclass:write']],
            security: 'is_granted("ROLE_ADMIN") or (is_granted("ROLE_TEACHER") and object.getAuthor() == user)',
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['masterclass:list']],
            security: 'is_granted("PUBLIC_ACCESS")',
        ),
        new Post(
            denormalizationContext: ['groups' => ['masterclass:write']],
            security: 'is_granted("ROLE_ADMIN") or is_granted("ROLE_TEACHER")',
        ),
    ],
    normalizationContext: ['groups' => ['masterclass:read']],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'title' => 'partial',
    'author.username' => 'partial',
    'categories.id' => 'exact',
    'tags.id' => 'exact',
    'difficultyLevel' => 'exact',
])]
#[ApiFilter(RangeFilter::class, properties: ['price'])] // todo add custom rating filter
class Masterclass implements EntityTimestampInterface
{
    const DIFFICULTY_LEVEL_BEGINNER = 1;
    const DIFFICULTY_LEVEL_INTERMEDIATE = 2;
    const DIFFICULTY_LEVEL_ADVANCED = 3;
    const DIFFICULTY_LEVEL_EXPERT = 4;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['masterclass:read', 'masterclass:list'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['masterclass:read', 'masterclass:write', 'masterclass:list'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['masterclass:read', 'masterclass:write', 'masterclass:list'])]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: 'The url {{ value }} is not a valid url')]
    #[Groups(['masterclass:read', 'masterclass:write', 'masterclass:list'])]
    private ?string $thumbnailUrl = null;

    #[ORM\ManyToOne(inversedBy: 'masterclasses')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['masterclass:read', 'masterclass:list'])]
    private ?User $author = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['masterclass:read', 'masterclass:write', 'masterclass:list'])]
    private ?string $price = null;

    #[ORM\Column]
    #[Groups(['masterclass:read'])]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['masterclass:read'])]
    private ?DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'masterclass', targetEntity: Enrollment::class)]
    private Collection $enrollments;

    #[ORM\OneToMany(mappedBy: 'masterclass', targetEntity: Lesson::class, orphanRemoval: true)]
    #[Groups(['masterclass:read'])]
    private Collection $lessons;

    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'masterclasses')]
    #[Groups(['masterclass:read', 'masterclass:write', 'masterclass:list'])]
    private Collection $categories;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'masterclasses')]
    #[Groups(['masterclass:read', 'masterclass:write', 'masterclass:list'])]
    private Collection $tags;

    #[ORM\OneToMany(mappedBy: 'masterclass', targetEntity: Rating::class)]
    #[Groups(['masterclass:read', 'masterclass:list'])]
    private Collection $ratings;

    #[ORM\Column(nullable: false)]
    #[Assert\Choice(callback: 'getDifficultyLevelList', message: 'Choose a valid difficulty level')]
    #[Groups(['masterclass:read', 'masterclass:write', 'masterclass:list'])]
    private ?int $difficultyLevel = self::DIFFICULTY_LEVEL_INTERMEDIATE;

    #[Groups(['masterclass:read', 'masterclass:list'])]
    private ?float $averageRating = null;

    public function __construct()
    {
        $this->enrollments = new ArrayCollection();
        $this->lessons = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->ratings = new ArrayCollection();
    }

    public static function getDifficultyLevelList(): array
    {
        return [
            self::DIFFICULTY_LEVEL_BEGINNER,
            self::DIFFICULTY_LEVEL_INTERMEDIATE,
            self::DIFFICULTY_LEVEL_ADVANCED,
            self::DIFFICULTY_LEVEL_EXPERT,
        ];
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

    public function getThumbnailUrl(): ?string
    {
        return $this->thumbnailUrl;
    }

    public function setThumbnailUrl(?string $thumbnailUrl): static
    {
        $this->thumbnailUrl = $thumbnailUrl;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

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
     * @return Collection<int, Enrollment>
     */
    public function getEnrollments(): Collection
    {
        return $this->enrollments;
    }

    public function addEnrollment(Enrollment $enrollment): static
    {
        if (!$this->enrollments->contains($enrollment)) {
            $this->enrollments->add($enrollment);
            $enrollment->setMasterclass($this);
        }

        return $this;
    }

    public function removeEnrollment(Enrollment $enrollment): static
    {
        if ($this->enrollments->removeElement($enrollment)) {
            // set the owning side to null (unless already changed)
            if ($enrollment->getMasterclass() === $this) {
                $enrollment->setMasterclass(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Lesson>
     */
    public function getLessons(): Collection
    {
        return $this->lessons;
    }

    public function addLesson(Lesson $lesson): static
    {
        if (!$this->lessons->contains($lesson)) {
            $this->lessons->add($lesson);
            $lesson->setMasterclass($this);
        }

        return $this;
    }

    public function removeLesson(Lesson $lesson): static
    {
        if ($this->lessons->removeElement($lesson)) {
            // set the owning side to null (unless already changed)
            if ($lesson->getMasterclass() === $this) {
                $lesson->setMasterclass(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * @return Collection<int, Rating>
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(Rating $rating): static
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings->add($rating);
            $rating->setMasterclass($this);
        }

        return $this;
    }

    public function removeRating(Rating $rating): static
    {
        if ($this->ratings->removeElement($rating)) {
            // set the owning side to null (unless already changed)
            if ($rating->getMasterclass() === $this) {
                $rating->setMasterclass(null);
            }
        }

        return $this;
    }

    public function getAverageRating(): ?float
    {
        if ($this->averageRating === null) {
            $this->setAverageRating();
        }
        return $this->averageRating;
    }

    public function setAverageRating(): static
    {
        $averageRating = 0;

        if (count($this->ratings) === 0) {
            $this->averageRating = null;
        } else {
            foreach ($this->ratings as $rating) {
                $averageRating += $rating->getValue();
            }
            $this->averageRating = number_format($averageRating / count($this->ratings), 2);
        }

        return $this;
    }

    public function getDifficultyLevel(): ?int
    {
        return $this->difficultyLevel;
    }

    public function setDifficultyLevel(?int $difficultyLevel): static
    {
        $this->difficultyLevel = $difficultyLevel;

        return $this;
    }
}
