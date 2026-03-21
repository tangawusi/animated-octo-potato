<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ConfirmationEmailWriter;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class RegistrationController extends AbstractController
{
    /**
     * @Route("/api/register", name="api_register", methods={"POST"})
     */
    public function register(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ConfirmationEmailWriter $confirmationEmailWriter,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse {
        try {
            /** @var array<string, mixed> $payload */
            $payload = json_decode((string) $request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            return $this->json([
                'message' => 'Invalid JSON payload.',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $email = mb_strtolower(trim((string) ($payload['email'] ?? '')));
        $password = (string) ($payload['password'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json([
                'message' => 'A valid email address is required.',
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (mb_strlen($password) < 8) {
            return $this->json([
                'message' => 'Password must be at least 8 characters long.',
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($userRepository->findOneByEmail($email) instanceof User) {
            return $this->json([
                'message' => 'An account with this email already exists.',
            ], JsonResponse::HTTP_CONFLICT);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($passwordHasher->hashPassword($user, $password));
        $user->refreshConfirmationToken();

        $entityManager->persist($user);
        $entityManager->flush();

        $confirmationUrl = $urlGenerator->generate(
            'app_confirm_account',
            ['token' => $user->getConfirmationToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $confirmationEmailWriter->writeConfirmationEmail($user->getEmail(), $confirmationUrl);

        return $this->json([
            'message' => 'Registration successful. Open the latest file in var/emails and click the confirmation link.',
        ], JsonResponse::HTTP_CREATED);
    }
}
