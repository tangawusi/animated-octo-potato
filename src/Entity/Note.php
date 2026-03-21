<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\NoteRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NoteRepository::class)
 * @ORM\Table(
 *     name="note",
 *     indexes={
 *         @ORM\Index(name="idx_note_status", columns={"status"}),
 *         @ORM\Index(name="idx_note_category", columns={"category"}),
 *         @ORM\Index(name="idx_note_created_at", columns={"created_at"})
 *     }
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class Note
{
    public const STATUS_NEW = 'new';
    public const STATUS_TODO = 'todo';
    public const STATUS_DONE = 'done';

    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_TODO,
        self::STATUS_DONE,
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="notes")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private ?User $owner = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $title = '';

    /**
     * @ORM\Column(type="text")
     */
    private string $content = '';

    /**
     * @ORM\Column(type="string", length=100)
     */
    private string $category = '';

    /**
     * @ORM\Column(type="string", length=20)
     */
    private string $status = self::STATUS_NEW;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private ?DateTimeImmutable $createdAt = null;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private ?DateTimeImmutable $updatedAt = null;

    public static function isValidStatus(string $status): bool
    {
        return in_array($status, self::STATUSES, true);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = trim($title);

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = trim($content);

        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = trim($category);

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        if (!self::isValidStatus($status)) {
            throw new \InvalidArgumentException('Invalid note status.');
        }

        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist(): void
    {
        $now = new DateTimeImmutable();

        $this->createdAt = $this->createdAt ?? $now;
        $this->updatedAt = $this->updatedAt ?? $now;
    }

    /**
     * @ORM\PreUpdate
     */
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * @return array<string, int|string|null>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'content' => $this->getContent(),
            'category' => $this->getCategory(),
            'status' => $this->getStatus(),
            'createdAt' => $this->getCreatedAt() !== null ? $this->getCreatedAt()->format(DATE_ATOM) : null,
            'updatedAt' => $this->getUpdatedAt() !== null ? $this->getUpdatedAt()->format(DATE_ATOM) : null,
        ];
    }
}
