<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\BadgeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BadgeRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            security: 'is_granted("IS_AUTHENTICATED_FULLY")',
        ),
        new Put(),
        new Delete(),
        new Patch(),
        new GetCollection(
            security: 'is_granted("IS_AUTHENTICATED_FULLY")',
        ),
        new Post(
            denormalizationContext: ['groups' => ['badge:write']],
        ),
    ],
    normalizationContext: ['groups' => ['badge:read']],
    security: 'is_granted("ROLE_ADMIN")',
)]
class Badge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['badge:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['badge:read', 'badge:write'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['badge:read', 'badge:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Assert\Url(message: 'The url {{ value }} is not a valid url')]
    #[Groups(['badge:read', 'badge:write'])]
    private ?string $imageUrl = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'badges')]
    private Collection $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addBadge($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeBadge($this);
        }

        return $this;
    }
}
