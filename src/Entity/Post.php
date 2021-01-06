<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints\Timezone;
use OpenApi\Annotations;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\PostRepository;

/**
 * @ORM\Cache("NONSTRICT_READ_WRITE")
 * @ORM\Entity(repositoryClass=PostRepository::class)
 */
class Post
{
  /**
   * @ORM\Id
   * @ORM\Column(type="uuid_binary")
   * @var \Ramsey\Uuid\UuidInterface
   */
  private $id;

  /**
   * @ORM\Column(type="text", nullable=true)
   * @var string|null
   */
  private $text;

  /**
   * @ORM\ManyToMany(targetEntity=Document::class, cascade={"persist"})
   * @var \Doctrine\Common\Collections\Collection|ArrayCollection|Document[]
   */
  private $documents;

  /**
   * @ORM\Column(type="datetime")
   * @var \DateTimeInterface|null
   */
  private $createdAt;

  /**
   * @ORM\ManyToMany(targetEntity=Photo::class)
   * @var \Doctrine\Common\Collections\Collection|ArrayCollection|Photo[]
   */
  private $photos;

  public function __construct()
  {
    $this->id = \Ramsey\Uuid\Uuid::uuid4();
    $this->documents = new ArrayCollection();
    $this->createdAt = $this->createdAt ?? new \DateTime();
    $this->photos = new ArrayCollection();
  }

  public function getId(): \Ramsey\Uuid\UuidInterface
  {
    return $this->id;
  }

  public function getText(): ?string
  {
    return $this->text;
  }

  public function setText(?string $text): self
  {
    $this->text = $text;

    return $this;
  }

  /**
   * @return Collection|Document[]
   */
  public function getDocuments(): Collection
  {
    return $this->documents;
  }

  /**
   * @param Document[] $documents
   * @return self
   */
  public function setDocuments(array $documents): self
  {
    $collection = new ArrayCollection($documents);
    $this->documents = $collection;
    return $this;
  }

  public function addDocument(Document $document): self
  {
    if (!$this->documents->contains($document)) {
      $this->documents[] = $document;
    }

    return $this;
  }

  public function removeDocument(Document $document): self
  {
    $this->documents->removeElement($document);

    return $this;
  }

  public function getCreatedAt(): \DateTimeInterface
  {
    return $this->createdAt;
  }

  public function setCreatedAt(\DateTimeInterface $createdAt): self
  {
    $this->createdAt = $createdAt;

    return $this;
  }

  /**
   * @return Collection|Photo[]
   */
  public function getPhotos(): Collection
  {
    return $this->photos;
  }

  public function addPhoto(Photo $photo): self
  {
    if (!$this->photos->contains($photo)) {
      $this->photos[] = $photo;
    }

    return $this;
  }

  public function removePhoto(Photo $photo): self
  {
    $this->photos->removeElement($photo);

    return $this;
  }
}
