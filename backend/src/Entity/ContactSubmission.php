<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'contact_submission')]
class ContactSubmission
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'El nombre es obligatorio')]
    #[Assert\Length(max: 255)]
    private string $name;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'El email es obligatorio')]
    #[Assert\Email(message: 'El email no es válido')]
    private string $email;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $eventType = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'El mensaje es obligatorio')]
    #[Assert\Length(max: 2000)]
    private string $message;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $submittedAt;

    #[ORM\Column(type: 'boolean')]
    private bool $emailSent = false;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $emailSentAt = null;

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
    public function getSubmittedAt(): \DateTimeImmutable { return $this->submittedAt; }
    public function isEmailSent(): bool { return $this->emailSent; }

    public function markEmailSent(): void
    {
        $this->emailSent = true;
        $this->emailSentAt = new \DateTimeImmutable();
    }
}
