<?php

declare(strict_types=1);

namespace App\Service;

final class ConfirmationEmailWriter
{
    private string $targetDirectory;

    public function __construct(string $projectDir)
    {
        $this->targetDirectory = $projectDir.'/var/emails';
    }

    public function writeConfirmationEmail(string $recipient, string $confirmationUrl): string
    {
        if (!is_dir($this->targetDirectory) && !mkdir($concurrentDirectory = $this->targetDirectory, 0775, true) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Unable to create directory "%s".', $this->targetDirectory));
        }

        $safeRecipient = preg_replace('/[^a-z0-9]+/i', '-', $recipient);
        $timestamp = (new \DateTimeImmutable())->format('Ymd_His');
        $path = sprintf('%s/%s_%s.html', $this->targetDirectory, $timestamp, trim((string) $safeRecipient, '-'));

        $body = sprintf(
            "<html><body><h1>Confirm your account</h1><p>Email: %s</p><p><a href=\"%s\">Confirm account</a></p><p>If the link does not open, copy this URL into your browser:</p><pre>%s</pre></body></html>",
            htmlspecialchars($recipient, ENT_QUOTES),
            htmlspecialchars($confirmationUrl, ENT_QUOTES),
            htmlspecialchars($confirmationUrl, ENT_QUOTES)
        );

        file_put_contents($path, $body);

        return $path;
    }
}
