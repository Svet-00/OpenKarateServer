<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\LinkRepository;

/**
 * @ORM\Entity(repositoryClass=LinkRepository::class)
 */
class Link
{
  /**
   * @ORM\Id
   * @ORM\Column(type="uuid_binary")
   * @var \Ramsey\Uuid\UuidInterface
   */
  private $id;

  /**
   * @ORM\Column(type="text")
   * @var string|null
   */
  private $url;

  /**
   * @ORM\Column(type="string", length=255, nullable=true)
   * @var string|null
   */
  private $title;

  public function getId(): \Ramsey\Uuid\UuidInterface
  {
    return $this->id;
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

  public function getTitle(): ?string
  {
    return $this->title;
  }

  public function setTitle(string $title): self
  {
    $this->title = $title;

    return $this;
  }
  public function __construct()
  {
    $this->id = \Ramsey\Uuid\Uuid::uuid4();
  }
}
