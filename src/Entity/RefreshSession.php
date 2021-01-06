<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\RefreshSessionRepository;

/**
 * @ORM\Entity(repositoryClass=RefreshSessionRepository::class)
 */
class RefreshSession
{
  /**
   * @ORM\Id
   * @ORM\Column(type="uuid_binary")
   * @var \Ramsey\Uuid\UuidInterface
   */
  private $id;

  /**
   * @ORM\ManyToOne(targetEntity=User::class, inversedBy="refreshSessions")
   * @ORM\JoinColumn(nullable=false)
   * @var User|null
   */
  private $user;

  /**
   * @ORM\Column(type="string", length=190, unique=true)
   * @var string|null
   */
  private $refreshToken;

  /**
   * @ORM\Column(type="string", length=255)
   * @var string|null
   */
  private $fingerprint;

  /**
   * @ORM\Column(type="datetime")
   */
  private $expiresIn;

  public function getId(): \Ramsey\Uuid\UuidInterface
  {
    return $this->id;
  }

  public function getUser(): ?User
  {
    return $this->user;
  }

  public function setUser(?User $user): self
  {
    $this->user = $user;

    return $this;
  }

  public function getRefreshToken(): ?string
  {
    return $this->refreshToken;
  }

  public function setRefreshToken(string $refreshToken): self
  {
    $this->refreshToken = $refreshToken;

    return $this;
  }

  public function getFingerprint(): ?string
  {
    return $this->fingerprint;
  }

  public function setFingerprint(string $fingerprint): self
  {
    $this->fingerprint = $fingerprint;

    return $this;
  }

  /**
   * Get the value of expiresIn
   */
  public function getExpiresIn()
  {
    return $this->expiresIn;
  }

  /**
   * Set the value of expiresIn
   */
  public function setExpiresIn($expiresIn): self
  {
    $this->expiresIn = $expiresIn;

    return $this;
  }
  public function __construct()
  {
    $this->id = \Ramsey\Uuid\Uuid::uuid4();
  }
}
