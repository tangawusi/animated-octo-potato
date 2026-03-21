<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

final class ApiAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function start(Request $request, AuthenticationException $authException = null): JsonResponse
    {
        if (preg_match('#^/api#', (string) $request->getPathInfo()) === 1) {
            return new JsonResponse([
                'message' => 'Authentication is required.',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'message' => 'Authentication is required.',
        ], JsonResponse::HTTP_UNAUTHORIZED);
    }
}
