<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsController]
class RegisterUser extends AbstractController
{
    const REQUIRED_FIELDS = ['username', 'email', 'password'];

    public function __invoke(
        Request                        $request,
        PasswordHasherFactoryInterface $passwordHasher,
        EntityManagerInterface         $manager,
        ValidatorInterface             $validator
    ): JsonResponse
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
            ->setRoles(['ROLE_USER'])
            ->setPassword($passwordHasher->hash($content['password']))
            ->setUsername($content['username'])
            ->setFirstName($content['firstName'] ?? null)
            ->setLastName($content['lastName'] ?? null)
            ->setBiography($content['biography'] ?? null)
            ->setProfilePicture($content['profilePicture'] ?? null);

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
            'message' => 'User successfully registered',
        ]);
    }
}
