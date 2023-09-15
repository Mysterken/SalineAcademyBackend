<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

#[AsController]
class UpdateUserPassword extends AbstractController
{

    const REQUIRED_FIELDS = ['oldPassword', 'newPassword'];

    public function __invoke(
        User $user,
        Security $security,
        Request $request,
        PasswordHasherFactoryInterface $passwordHasher,
        EntityManagerInterface $manager
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

        /* @var User $userLogged */
        $userLogged = $security->getUser();

        if (
            !$userLogged ||
            (
                $userLogged->getId() != $user->getId() &&
                !$userLogged->isAdmin()
            )
        ) {
            return $this->json([
                'status' => 'error',
                'message' => 'You are not allowed to update this user',
            ], 403);
        }

        if (
            !$passwordHasher->getPasswordHasher(User::class)->verify($userLogged->getPassword(), $content['oldPassword']) &&
            !$userLogged->isAdmin()
        ) {
            return $this->json([
                'status' => 'error',
                'message' => 'Old password is not correct',
            ], 400);
        }

        $passwordHasher = $passwordHasher->getPasswordHasher(User::class);

        $user->setPassword($passwordHasher->hash($content['newPassword']));

        $manager->persist($user);
        $manager->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Password successfully updated',
        ], 200);
    }
}
