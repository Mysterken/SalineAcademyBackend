<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route("/api/register", name: "register", methods: ["POST"])]
class RegisterController extends AbstractController
{
    const REQUIRED_FIELDS = ['username', 'email', 'password'];

    public function __invoke(Request $request, PasswordHasherFactoryInterface $passwordHasher, EntityManagerInterface $manager, ValidatorInterface $validator): JsonResponse
    {
        $content = json_decode($request->getContent(), true);

        foreach (self::REQUIRED_FIELDS as $field) {
            if (!isset($content[$field])) {
                return $this->json([
                    'status' => 'error',
                    'message' => "Missing required field '$field'",
                ], 400);
            }
        }

        if ($manager->getRepository(User::class)->findOneBy(['email' => $content['email']])) {
            return $this->json([
                'status' => 'error',
                'message' => 'Email already used',
            ], 400);
        }

        $passwordHasher = $passwordHasher->getPasswordHasher(User::class);

        $user = new User();
        $user
            ->setEmail($content['email'])
            ->setPassword($passwordHasher->hash($content['password']))
            ->setFirstName($content['firstName'] ?? null)
            ->setLastName($content['lastName'] ?? null)
            ->setUsername($content['username'])
            ->setBiography($content['biography'] ?? null)
            ->setProfilePicture($content['profilePicture'] ?? null)
            ->setCreatedAt()
            ->setUpdatedAt();

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid data',
                'errors' => $errors,
            ], 400);
        }

        $manager->persist($user);
        $manager->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'User created successfully',
        ]);
    }
}
