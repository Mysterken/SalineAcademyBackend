<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            security: 'is_granted("PUBLIC_ACCESS")',
        ),
        new Put(),
        new Delete(),
        new Patch(),
        new GetCollection(
            security: 'is_granted("PUBLIC_ACCESS")',
        ),
        new Post(
            denormalizationContext: ['groups' => ['tag:write']],
        ),
    ],
    normalizationContext: ['groups' => ['tag:list']],
    security: 'is_granted("ROLE_ADMIN")',
)]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['tag:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['tag:read', 'tag:write', 'masterclass:list', 'masterclass:read'])]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: Masterclass::class, mappedBy: 'tags')]
    private Collection $masterclasses;

    public function __construct()
    {
        $this->masterclasses = new ArrayCollection();
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
            $masterclass->addTag($this);
        }

        return $this;
    }

    public function removeMasterclass(Masterclass $masterclass): static
    {
        if ($this->masterclasses->removeElement($masterclass)) {
            $masterclass->removeTag($this);
        }

        return $this;
    }
}
