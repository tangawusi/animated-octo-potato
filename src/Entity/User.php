<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(
 * name="app_user",
 * uniqueConstraints={
 * @ORM\UniqueConstraint(name="uniq_app_user_email", columns={"email"}),
 * @ORM\UniqueConstraint(name="uniq_app_user_confirmation_token", columns={"confirmation_token"})
 * }
 * )
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private string $email = '';

    /**
     * @var string[]
     *
     * @ORM\Column(type="json")
     */
    private array $roles = [];

    /**
     * @ORM\Column(type="string")
     */
    private string $password = '';

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isVerified = false;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private ?string $confirmationToken = null;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?DateTimeImmutable $confirmationTokenExpiresAt = null;

    /**
     * @var Collection<int, Note>
     *
     * @ORM\OneToMany(targetEntity=Note::class, mappedBy="owner", orphanRemoval=true)
     */
    private Collection $notes;

    public function __construct()
    {
        $this->notes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = mb_strtolower(trim($email));

        return $this;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): self
    {
        $cleanRoles = [];

        foreach ($roles as $role) {
            $role = trim((string) $role);

            if ($role !== '') {
                $cleanRoles[] = $role;
            }
        }

        $this->roles = array_values(array_unique($cleanRoles));

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function markAsVerified(): self
    {
        $this->isVerified = true;
        $this->confirmationToken = null;
        $this->confirmationTokenExpiresAt = null;

        return $this;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function getConfirmationTokenExpiresAt(): ?DateTimeImmutable
    {
        return $this->confirmationTokenExpiresAt;
    }

    public function refreshConfirmationToken(): self
    {
        $this->isVerified = false;
        $this->confirmationToken = bin2hex(random_bytes(32));
        $this->confirmationTokenExpiresAt = (new DateTimeImmutable())->add(new DateInterval('P1D'));

        return $this;
    }

    public function isVerificationExpired(): bool
    {
        if ($this->confirmationTokenExpiresAt === null) {
            return true;
        }

        return $this->confirmationTokenExpiresAt < new DateTimeImmutable();
    }

    /**
     * @return Collection<int, Note>
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes->add($note);
            $note->setOwner($this);
        }

        return $this;
    }

    public function removeNote(Note $note): self
    {
        if ($this->notes->removeElement($note) && $note->getOwner() === $this) {
            $note->setOwner(null);
        }

        return $this;
    }
}
