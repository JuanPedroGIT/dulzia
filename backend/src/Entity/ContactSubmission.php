<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

// Sin constraints de validación: la frontera de validación es
// SubmitContactCommand (Application) — una única fuente de verdad.
#[ORM\Entity]
#[ORM\Table(name: 'contact_submission')]
class ContactSubmission
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255)]
    private string $email;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $eventType = null;

    #[ORM\Column(type: 'text')]
    private string $message;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $submittedAt;

    #[ORM\Column(type: 'boolean')]
    private bool $emailSent = false;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $emailSentAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $readAt = null;

    public function __construct(
        string $name,
        string $email,
        string $message,
        ?string $phone = null,
        ?string $eventType = null,
        ?string $ipAddress = null,
    ) {
        $this->id = bin2hex(random_bytes(16));
        $this->name = $name;
        $this->email = $email;
        $this->message = $message;
        $this->phone = $phone;
        $this->eventType = $eventType;
        $this->ipAddress = $ipAddress;
        $this->submittedAt = new \DateTimeImmutable();
    }

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getPhone(): ?string { return $this->phone; }
    public function getEventType(): ?string { return $this->eventType; }
    public function getMessage(): string { return $this->message; }
    public function getIpAddress(): ?string { return $this->ipAddress; }
    public function getSubmittedAt(): \DateTimeImmutable { return $this->submittedAt; }
    public function isEmailSent(): bool { return $this->emailSent; }
    public function getEmailSentAt(): ?\DateTimeImmutable { return $this->emailSentAt; }
    public function getReadAt(): ?\DateTimeImmutable { return $this->readAt; }
    public function isRead(): bool { return $this->readAt !== null; }

    public function markEmailSent(): void
    {
        $this->emailSent = true;
        $this->emailSentAt = new \DateTimeImmutable();
    }

    public function markRead(): void
    {
        $this->readAt = new \DateTimeImmutable();
    }

    public function markUnread(): void
    {
        $this->readAt = null;
    }
}
