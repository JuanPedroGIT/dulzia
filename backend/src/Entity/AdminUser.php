<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'admin_user')]
class AdminUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(length: 100, unique: true)]
    private string $username;

    #[ORM\Column(length: 255)]
    private string $passwordHash;

    public function __construct(string $username, string $passwordHash)
    {
        $this->username = $username;
        $this->passwordHash = $passwordHash;
    }

    public function getId(): int { return $this->id; }
    public function getUsername(): string { return $this->username; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function setPasswordHash(string $hash): void { $this->passwordHash = $hash; }
}
