<?php

namespace App\Entity;

use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Schedule;

/**
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 * @ORM\Entity(repositoryClass="App\Repository\GymRepository")
 */
class Gym
{
  /**
   * @ORM\Id()
   * @ORM\Column(type="uuid_binary")
   * @Groups("summary")
   * @var \Ramsey\Uuid\UuidInterface
   */
  private $id;

  /**
   * @ORM\Column(type="string", length=100)
   * @Groups("summary")
   * @var string|null
   */
  private $address;

  /**
   * @ORM\Column(type="string", length=190, unique=true)
   * @Groups("summary")
   * @var string|null
   */
  private $title;

  /**
   * @ORM\Column(type="text")
   * @var string|null
   */
  private $description;

  /**
   * @ORM\Column(type="text")
   * @var string|null
   */
  private $workingTime;

  /**
   * @ORM\Column(type="string", length=255)
   * @var string|null
   */
  private $phoneNumber;

  /**
   * @ORM\Column(type="string", length=255, nullable=true)
   * @var string|null
   */
  private $email;

  /**
   * @ORM\Column(type="string", length=255)
   * @var string|null
   */
  private $vkLink;

  /**
   * @ORM\OneToMany(targetEntity=Schedule::class, mappedBy="gym", orphanRemoval=true,  cascade={"persist"})
   * @var \Doctrine\Common\Collections\Collection|ArrayCollection|Schedule[]
   */
  private $schedules;

  /**
   * @ORM\ManyToMany(targetEntity=Photo::class)
   * @var \Doctrine\Common\Collections\Collection|ArrayCollection|Photo[]
   */
  private $photos;

  /** @var bool|null */
  private $isFavorite;

  public function __construct()
  {
    $this->id = \Ramsey\Uuid\Uuid::uuid4();
    $this->schedules = new ArrayCollection();
    $this->photos = new ArrayCollection();
  }

  public function getId(): \Ramsey\Uuid\UuidInterface
  {
    return $this->id;
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

  public function setDescription(string $description): self
  {
    $this->description = $description;

    return $this;
  }

  public function getWorkingTime(): ?string
  {
    return $this->workingTime;
  }

  public function setWorkingTime(string $workingTime): self
  {
    $this->workingTime = $workingTime;

    return $this;
  }

  public function getPhoneNumber(): ?string
  {
    return $this->phoneNumber;
  }

  public function setPhoneNumber(string $phoneNumber): self
  {
    $this->phoneNumber = $phoneNumber;

    return $this;
  }

  public function getEmail(): ?string
  {
    return $this->email;
  }

  public function setEmail(?string $email): self
  {
    $this->email = $email;

    return $this;
  }

  public function getVkLink(): ?string
  {
    return $this->vkLink;
  }

  public function setVkLink(string $vkLink): self
  {
    $this->vkLink = $vkLink;

    return $this;
  }

  /**
   * @return Collection|Schedule[]
   */
  public function getSchedules(): Collection
  {
    return $this->schedules;
  }

  public function addSchedule(Schedule $schedule): self
  {
    if (!$this->schedules->contains($schedule)) {
      $this->schedules[] = $schedule;
      $schedule->setGym($this);
    }

    return $this;
  }

  public function removeSchedule(Schedule $schedule): self
  {
    if ($this->schedules->contains($schedule)) {
      $this->schedules->removeElement($schedule);
      // set the owning side to null (unless already changed)
      if ($schedule->getGym() === $this) {
        $schedule->setGym(null);
      }
    }

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

  public function getIsFavorite(): ?bool
  {
    return $this->isFavorite;
  }

  public function setIsFavorite(bool $isFavorite): self
  {
    $this->isFavorite = $isFavorite;

    return $this;
  }
}
