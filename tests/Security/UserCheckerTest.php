<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

final class UserCheckerTest extends TestCase
{
    public function testItRejectsAnUnverifiedUser(): void
    {
        $user = new User();
        $user->setEmail('tester@example.com');
        $user->setPassword('hashed-password');

        $checker = new UserChecker();

        $this->expectException(CustomUserMessageAccountStatusException::class);
        $checker->checkPreAuth($user);
    }

    public function testItAllowsVerifiedUsers(): void
    {
        $user = new User();
        $user->setEmail('tester@example.com');
        $user->setPassword('hashed-password');
        $user->refreshConfirmationToken();
        $user->markAsVerified();

        $checker = new UserChecker();
        $checker->checkPreAuth($user);

        $this->assertTrue(true);
    }
}
