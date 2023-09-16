<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use App\Controller\AddPoint;
use App\Repository\PointRepository;
use ArrayObject;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PointRepository::class)]
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/points',
            controller: AddPoint::class,
            openapi: new Operation(
                responses: [
                    '200' => [
                        'description' => 'Points added',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'status' => [
                                            'type' => 'string',
                                            'example' => 'success',
                                        ],
                                        'message' => [
                                            'type' => 'string',
                                            'example' => 'Added 100 points to user',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '400' => [
                        'description' => 'Invalid data',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'status' => [
                                            'type' => 'string',
                                            'example' => 'error',
                                        ],
                                        'message' => [
                                            'type' => 'string',
                                            'example' => 'Invalid data',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                summary: 'Add points to a user',
                description: 'Add points to a user',
                requestBody: new RequestBody(
                    content: new ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'amount' => [
                                        'type' => 'int',
                                        'example' => '100',
                                    ],
                                ],
                            ],
                        ],
                    ])
                ),
            ),
            security: 'is_granted("ROLE_USER")',
            name: 'add_point',
        )
    ]
)]
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
