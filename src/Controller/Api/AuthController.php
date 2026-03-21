<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class AuthController extends AbstractController
{
    /**
     * @Route("/api/me", name="api_me", methods={"GET"})
     */
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json([
                'authenticated' => false,
                'user' => null,
            ]);
        }

        return $this->json([
            'authenticated' => true,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'verified' => $user->isVerified(),
            ],
        ]);
    }

    /**
     * @Route("/api/logout", name="api_logout", methods={"POST"})
     */
    public function logout(): void
    {
        throw new \LogicException('Logout is handled by the firewall.');
    }
}
