<?php

namespace App\Entity;

use App\Repository\PointRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PointRepository::class)]
class Point
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'points')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?int $amount = null;

    #[ORM\Column]
    private ?DateTimeImmutable $earnedDate = null;

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

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getEarnedDate(): ?DateTimeImmutable
    {
        return $this->earnedDate;
    }

    public function setEarnedDate(DateTimeImmutable $earnedDate): static
    {
        $this->earnedDate = $earnedDate;

        return $this;
    }
}
