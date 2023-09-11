<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\EventListener\RatingListener;
use App\Repository\RatingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RatingRepository::class)]
#[ORM\EntityListeners([RatingListener::class])]
#[ApiResource(
    operations: [
        new Get(),
        new Put(
            denormalizationContext: ['groups' => ['rating:update']],
        ),
        new Delete(),
        new Patch(
            denormalizationContext: ['groups' => ['rating:update']],
        ),
        new GetCollection(
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Post(
            denormalizationContext: ['groups' => ['rating:write']],
            security: 'is_granted("ROLE_ADMIN") or is_granted("ROLE_USER")',
        ),
    ],
    normalizationContext: ['groups' => ['rating:read']],
    security: 'is_granted("ROLE_ADMIN") or (is_granted("ROLE_USER") and object.getAuthor() === user)',
)]
class Rating
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['progress:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'ratings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['rating:read'])]
    private ?User $author = null;

    #[ORM\Column]
    #[Assert\Range(notInRangeMessage: 'The rating must be between 0 and 5', min: '0', max: '5')]
    #[Groups(['rating:read', 'rating:write', 'rating:update'])]
    private ?float $value = null;

    #[ORM\ManyToOne(inversedBy: 'ratings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['rating:read', 'rating:write'])]
    private ?Masterclass $masterclass = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(float $value): static
    {
        $this->value = $value;

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
