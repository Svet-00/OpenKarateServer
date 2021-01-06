<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Gym;

/**
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 * @ORM\Entity(repositoryClass="App\Repository\ScheduleRepository")
 */
class Schedule
{
  /**
   * @ORM\Id()
   * @ORM\Column(type="uuid_binary")
   * @var \Ramsey\Uuid\UuidInterface
   */
  private $id;

  /**
   * @ORM\ManyToOne(targetEntity=Gym::class, inversedBy="schedules")
   * @ORM\JoinColumn(nullable=false)
   * @var Gym|null
   */
  private $gym;

  /**
   * @ORM\Column(type="string", length=15)
   * @var string|null
   */
  private $dayOfWeek;

  /**
   * @ORM\Column(type="string", length=255)
   * @var string|null
   */
  private $description;

  /**
   * @ORM\Column(type="string", length=255)
   * @var string|null
   */
  private $time;

  public function getId(): \Ramsey\Uuid\UuidInterface
  {
    return $this->id;
  }

  public function getGym(): ?Gym
  {
    return $this->gym;
  }

  public function setGym(?Gym $gym): self
  {
    $this->gym = $gym;

    return $this;
  }

  public function getDayOfWeek(): ?string
  {
    return $this->dayOfWeek;
  }

  public function setDayOfWeek(string $dayOfWeek): self
  {
    $this->dayOfWeek = $dayOfWeek;

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

  public function getTime(): ?string
  {
    return $this->time;
  }

  public function setTime(string $time): self
  {
    $this->time = $time;

    return $this;
  }
  public function __construct()
  {
    $this->id = \Ramsey\Uuid\Uuid::uuid4();
  }
}
