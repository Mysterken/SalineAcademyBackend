<?php

namespace App\Entity;

use DateTimeImmutable;
use DateTimeInterface;

interface EntityTimestampInterface
{
    public function getCreatedAt(): ?DateTimeImmutable;

    public function setCreatedAt(): static;

    public function getUpdatedAt(): ?DateTimeInterface;

    public function setUpdatedAt(): static;
}
