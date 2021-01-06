<?php

namespace App\Entity;

use Ramsey\Uuid\UuidInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use DateTime;
use App\Repository\EventRepository;

/**
 * @ORM\Cache("NONSTRICT_READ_WRITE")
 * @ORM\Entity(repositoryClass=EventRepository::class)
 */
class Event
{
  /**
   * @ORM\Id
   * @ORM\Column(type="uuid_binary")
   * @var UuidInterface
   */
  private $id;

  /**
   * @ORM\Column(type="string", length=255)
   * @var string|null
   */
  private $title;

  /**
   * @ORM\Column(type="text", nullable=true)
   * @var string|null
   */
  private $description;

  /**
   * @ORM\Column(type="string", length=255)
   * @var string|null
   */
  private $address;

  /**
   * @ORM\Column(type="datetime")
   * @var \DateTimeInterface
   */
  private $startDate;

  /**
   * @ORM\Column(type="datetime")
   * @var \DateTimeInterface
   */
  private $endDate;

  /**
   * @ORM\Column(type="boolean")
   * @var bool|null
   */
  private $isCanceled = false;

  /**
   * @ORM\ManyToMany(targetEntity=User::class, inversedBy="events")
   * @var ArrayCollection|User[]
   */
  private $participants;

  /**
   * @ORM\Column(type="string", length=50)
   * @var string
   */
  private $type;

  /**
   * @ORM\Column(type="string", length=100)
   * @var string
   */
  private $level;

  /**
   * @ORM\ManyToMany(targetEntity=Document::class)
   * @var ArrayCollection|Document[]
   */
  private $documents;

  /**
   * @ORM\ManyToMany(targetEntity=Link::class)
   * @var ArrayCollection|Link[]
   */
  private $links;

  public function __construct()
  {
    $this->id = \Ramsey\Uuid\Uuid::uuid4();
    $this->participants = new ArrayCollection();
    $this->documents = new ArrayCollection();
    $this->links = new ArrayCollection();
    $now = new DateTime('now');
    $this->startDate = $now;
    $this->endDate = $now;
  }

  public function getId(): UuidInterface
  {
    return $this->id;
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

  public function getDescription(): ?string
  {
    return $this->description;
  }

  public function setDescription(?string $description): self
  {
    $this->description = $description;

    return $this;
  }

  public function getAddress(): ?string
  {
    return $this->address;
  }

  public function setAddress(string $address): self
  {
    $this->address = $address;

    return $this;
  }

  public function getStartDate(): ?\DateTimeInterface
  {
    return $this->startDate;
  }

  public function setStartDate(\DateTimeInterface $startDate): self
  {
    $this->startDate = $startDate;

    return $this;
  }

  public function getEndDate(): \DateTime
  {
    return $this->endDate;
  }

  public function setEndDate(\DateTime $endDate): self
  {
    $this->endDate = $endDate;

    return $this;
  }

  public function getIsCanceled(): bool
  {
    return $this->isCanceled;
  }

  public function setIsCanceled(bool $isCanceled): self
  {
    $this->isCanceled = $isCanceled;

    return $this;
  }

  /**
   * @return Collection|User[]
   */
  public function getParticipants(): Collection
  {
    return $this->participants;
  }

  public function addParticipant(User $participant): self
  {
    if (!$this->participants->contains($participant)) {
      $this->participants[] = $participant;
    }

    return $this;
  }

  public function removeParticipant(User $participant): self
  {
    $this->participants->removeElement($participant);

    return $this;
  }

  public function getType(): ?string
  {
    return $this->type;
  }

  public function setType(string $type): self
  {
    $this->type = $type;

    return $this;
  }

  public function getLevel(): ?string
  {
    return $this->level;
  }

  public function setLevel(string $level): self
  {
    $this->level = $level;

    return $this;
  }

  /**
   * @return Collection|Document[]
   */
  public function getDocuments(): Collection
  {
    return $this->documents;
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

  /**
   * @return Collection|Link[]
   */
  public function getLinks(): Collection
  {
    return $this->links;
  }

  public function addLink(Link $link): self
  {
    if (!$this->links->contains($link)) {
      $this->links[] = $link;
    }

    return $this;
  }

  public function removeLink(Link $link): self
  {
    $this->links->removeElement($link);

    return $this;
  }
}
