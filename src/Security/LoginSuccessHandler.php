<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

final class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return new JsonResponse([
                'message' => 'Login succeeded.',
                'user' => null,
            ]);
        }

        return new JsonResponse([
            'message' => 'Login succeeded.',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'verified' => $user->isVerified(),
            ],
        ]);
    }
}
