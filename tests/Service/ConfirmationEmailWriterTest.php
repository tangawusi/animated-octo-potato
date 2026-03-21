<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\ConfirmationEmailWriter;
use PHPUnit\Framework\TestCase;

final class ConfirmationEmailWriterTest extends TestCase
{
    public function testItWritesAConfirmationEmailFile(): void
    {
        $projectDir = sys_get_temp_dir().'/aje-challenge-tests-'.bin2hex(random_bytes(4));
        mkdir($projectDir, 0775, true);

        $writer = new ConfirmationEmailWriter($projectDir);
        $path = $writer->writeConfirmationEmail('tester@example.com', 'http://localhost/confirm/token-123');

        $this->assertFileExists($path);
        $this->assertStringContainsString('tester@example.com', (string) file_get_contents($path));
        $this->assertStringContainsString('http://localhost/confirm/token-123', (string) file_get_contents($path));

        unlink($path);
        rmdir($projectDir.'/var/emails');
        rmdir($projectDir.'/var');
        rmdir($projectDir);
    }
}
