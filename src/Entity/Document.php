<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\DocumentRepository;

/**
 * @ORM\Entity(repositoryClass=DocumentRepository::class)
 */
class Document
{
  /**
   * @ORM\Id
   * @ORM\Column(type="uuid_binary")
   * @var \Ramsey\Uuid\UuidInterface
   */
  private $id;

  /**
   * @ORM\Column(type="string", length=255, nullable=false)
   * @var string|null
   */
  private $filename;

  /**
   * @ORM\Column(type="string", length=255, nullable=false)
   * @var string|null
   */
  private $originalFilename;

  /**
   * Generated dynamically during normalization
   * @var string|null
   */
  private $url;

  public function __construct()
  {
    $this->id = \Ramsey\Uuid\Uuid::uuid4();
  }

  public function getId(): \Ramsey\Uuid\UuidInterface
  {
    return $this->id;
  }

  public function getFilename(): ?string
  {
    return $this->filename;
  }

  public function setFilename(string $filename): self
  {
    $this->filename = $filename;

    return $this;
  }

  public function getOriginalFilename(): ?string
  {
    return $this->originalFilename;
  }

  public function setOriginalFilename(string $originalFilename): self
  {
    $this->originalFilename = $originalFilename;

    return $this;
  }

  public function getUrl(): ?string
  {
    return $this->url;
  }

  public function setUrl(string $url): self
  {
    $this->url = $url;

    return $this;
  }
}
