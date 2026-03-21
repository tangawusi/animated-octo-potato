<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

final class ConfirmationController extends AbstractController
{
    /**
     * @Route("/confirm/{token}", name="app_confirm_account", methods={"GET"})
     */
    public function confirm(
        string $token,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        $user = $userRepository->findOneByConfirmationToken($token);

        if (!$user instanceof User) {
            return $this->redirect('/?confirmation=invalid');
        }

        if ($user->isVerificationExpired()) {
            return $this->redirect('/?confirmation=expired');
        }

        $user->markAsVerified();
        $entityManager->flush();

        return $this->redirect('/?confirmation=success');
    }
}
