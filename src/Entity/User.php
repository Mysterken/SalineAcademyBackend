<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use App\Controller\RegisterUser;
use App\Controller\UpdateUserPassword;
use App\Repository\UserRepository;
use ArrayObject;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['user:read']],
        ),
        new Put(
            denormalizationContext: ['groups' => ['user:write']],
        ),
        new Delete(),
        new Patch(
            denormalizationContext: ['groups' => ['user:write']],
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['user:list']],
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Post(
            uriTemplate: '/register',
            controller: RegisterUser::class,
            openapi: new Operation(
                summary: 'Register a new user',
                description: 'Register a new user',
                requestBody: new RequestBody(
                    content: new ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'email' => [
                                        'type' => 'string',
                                        'example' => 'john@mail.com',
                                    ],
                                    'password' => [
                                        'type' => 'string',
                                        'example' => '123456',
                                    ],
                                    'username' => [
                                        'type' => 'string',
                                        'example' => 'johnDoe123',
                                    ],
                                    'firstName' => [
                                        'type' => 'string',
                                        'example' => 'John',
                                    ],
                                    'lastName' => [
                                        'type' => 'string',
                                        'example' => 'Doe',
                                    ],
                                    'biography' => [
                                        'type' => 'string',
                                        'example' => 'I am a web developer',
                                    ],
                                    'profilePicture' => [
                                        'type' => 'string',
                                        'example' => 'https://www.example.com/profile-picture.jpg',
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
            name: 'register_user',
        ),
        new Post(
            uriTemplate: '/users/{id}/password',
            controller: UpdateUserPassword::class,
            openapi: new Operation(
                summary: 'Update user password',
                description: 'Update user password',
                requestBody: new RequestBody(
                    content: new ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'oldPassword' => [
                                        'type' => 'string',
                                        'example' => '123456',
                                    ],
                                    'newPassword' => [
                                        'type' => 'string',
                                        'example' => '987654',
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
            name: 'update_user_password',
        )
    ],
    security: 'is_granted("ROLE_ADMIN") or object == user',
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, EntityTimestampInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read', 'user:list'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Email(message: 'The email {{ value }} is not a valid email.')]
    #[Groups(['user:read', 'user:list', 'user:write'])]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var ?string The hashed password
     */
    #[ORM\Column]
    #[Groups(['user:write'])]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write', 'user:list', 'masterclass:list'])]
    private ?string $username = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read', 'user:write', 'masterclass:list'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read', 'user:write', 'masterclass:list'])]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $biography = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: 'The url {{ value }} is not a valid url')]
    #[Groups(['user:read', 'user:write'])]
    private ?string $profilePicture = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Enrollment::class)]
    private Collection $enrollments;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Point::class, orphanRemoval: true)]
    private Collection $points;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Masterclass::class)]
    private Collection $masterclasses;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Progress::class)]
    private Collection $progress;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Rating::class)]
    private Collection $ratings;

    #[ORM\ManyToMany(targetEntity: Badge::class, inversedBy: 'users')]
    #[Groups(['user:read'])]
    private Collection $badges;

    #[ORM\ManyToMany(targetEntity: Achievement::class, inversedBy: 'users')]
    #[Groups(['user:read'])]
    private Collection $achievements;

    #[Groups(['user:read'])]
    private ?int $level = null;

    public function __construct()
    {
        $this->enrollments = new ArrayCollection();
        $this->points = new ArrayCollection();
        $this->masterclasses = new ArrayCollection();
        $this->progress = new ArrayCollection();
        $this->ratings = new ArrayCollection();
        $this->badges = new ArrayCollection();
        $this->achievements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->getRoles());
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role): static
    {
        $this->roles[] = $role;

        return $this;
    }

    #[ORM\PreUpdate]
    public function removeDuplicateRoles(): static
    {
        $this->roles = array_unique($this->roles);

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(?string $biography): static
    {
        $this->biography = $biography;

        return $this;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(?string $profilePicture): static
    {
        $this->profilePicture = $profilePicture;

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
            $enrollment->setUser($this);
        }

        return $this;
    }

    public function removeEnrollment(Enrollment $enrollment): static
    {
        if ($this->enrollments->removeElement($enrollment)) {
            // set the owning side to null (unless already changed)
            if ($enrollment->getUser() === $this) {
                $enrollment->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Point>
     */
    public function getPoints(): Collection
    {
        return $this->points;
    }

    public function addPoint(Point $point): static
    {
        if (!$this->points->contains($point)) {
            $this->points->add($point);
            $point->setUser($this);
        }

        return $this;
    }

    public function removePoint(Point $point): static
    {
        if ($this->points->removeElement($point)) {
            // set the owning side to null (unless already changed)
            if ($point->getUser() === $this) {
                $point->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Masterclass>
     */
    public function getMasterclasses(): Collection
    {
        return $this->masterclasses;
    }

    public function addMasterclass(Masterclass $masterclass): static
    {
        if (!$this->masterclasses->contains($masterclass)) {
            $this->masterclasses->add($masterclass);
            $masterclass->setAuthor($this);
        }

        return $this;
    }

    public function removeMasterclass(Masterclass $masterclass): static
    {
        if ($this->masterclasses->removeElement($masterclass)) {
            // set the owning side to null (unless already changed)
            if ($masterclass->getAuthor() === $this) {
                $masterclass->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Progress>
     */
    public function getProgress(): Collection
    {
        return $this->progress;
    }

    public function addProgress(Progress $progress): static
    {
        if (!$this->progress->contains($progress)) {
            $this->progress->add($progress);
            $progress->setUser($this);
        }

        return $this;
    }

    public function removeProgress(Progress $progress): static
    {
        if ($this->progress->removeElement($progress)) {
            // set the owning side to null (unless already changed)
            if ($progress->getUser() === $this) {
                $progress->setUser(null);
            }
        }

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
            $rating->setAuthor($this);
        }

        return $this;
    }

    public function removeRating(Rating $rating): static
    {
        if ($this->ratings->removeElement($rating)) {
            // set the owning side to null (unless already changed)
            if ($rating->getAuthor() === $this) {
                $rating->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Badge>
     */
    public function getBadges(): Collection
    {
        return $this->badges;
    }

    public function addBadge(Badge $badge): static
    {
        if (!$this->badges->contains($badge)) {
            $this->badges->add($badge);
        }

        return $this;
    }

    public function removeBadge(Badge $badge): static
    {
        $this->badges->removeElement($badge);

        return $this;
    }

    /**
     * @return Collection<int, Achievement>
     */
    public function getAchievements(): Collection
    {
        return $this->achievements;
    }

    public function addAchievement(Achievement $achievement): static
    {
        if (!$this->achievements->contains($achievement)) {
            $this->achievements->add($achievement);
        }

        return $this;
    }

    public function removeAchievement(Achievement $achievement): static
    {
        $this->achievements->removeElement($achievement);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLevel(): ?int
    {
        if ($this->level === null) {
            $this->setLevel();
        }
        return $this->level;
    }

    /**
     * @return User
     */
    public function setLevel(): User
    {
        $levelConstant = 50;
        $totalPoints = 0;

        /** @var Point $point */
        foreach ($this->points as $point) {
            $totalPoints += $point->getAmount();
        }

        $this->level = (int)floor($levelConstant * sqrt($totalPoints));
        return $this;
    }
}
