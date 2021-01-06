<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PhotoRepository;

/**
 * @ORM\Entity(repositoryClass=PhotoRepository::class)
 */
class Photo
{
  /**
   * @ORM\Id
   * @ORM\Column(type="uuid_binary")
   * @var \Ramsey\Uuid\UuidInterface
   */
  private $id;

  /**
   * @ORM\Column(type="string", length=255)
   * @var string|null
   */
  private $filename;

  /**
   * @ORM\Column(type="string", length=255)
   * @var string|null
   */
  private $serverFilename;

  /**
   * @ORM\Column(type="integer")
   * @var int|null
   */
  private $width;

  /**
   * @ORM\Column(type="integer")
   * @var int|null
   */
  private $height;

  /**
   * @ORM\Column(type="string", length=128)
   * @var string|null
   */
  private $mimeType;

  /**
   * @var string|null
   */
  private $url;

  /**
   * @ORM\Column(type="string", length=255)
   * @var string|null
   */
  private $bucket;

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

  public function getServerFilename(): ?string
  {
    return $this->serverFilename;
  }

  public function setServerFilename(string $serverFilename): self
  {
    $this->serverFilename = $serverFilename;

    return $this;
  }

  public function getWidth(): ?int
  {
    return $this->width;
  }

  public function setWidth(int $width): self
  {
    $this->width = $width;

    return $this;
  }

  public function getHeight(): ?int
  {
    return $this->height;
  }

  public function setHeight(int $height): self
  {
    $this->height = $height;

    return $this;
  }

  public function getMimeType(): ?string
  {
    return $this->mimeType;
  }

  public function setMimeType(string $mimeType): self
  {
    $this->mimeType = $mimeType;

    return $this;
  }

  public function getUrl(): ?string
  {
    return $this->url;
  }

  public function getBucket(): ?string
  {
    return $this->bucket;
  }

  public function setBucket(string $bucket): self
  {
    $this->bucket = $bucket;

    return $this;
  }
  public function __construct()
  {
    $this->id = \Ramsey\Uuid\Uuid::uuid4();
  }
}
