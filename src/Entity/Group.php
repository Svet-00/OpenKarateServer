<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\GroupRepository;

/**
 * @ORM\Entity(repositoryClass=GroupRepository::class)
 * @ORM\Table(name="`group`")
 */
class Group
{
  /**
   * @ORM\Id
   * @ORM\Column(type="uuid_binary")
   * @var \Ramsey\Uuid\UuidInterface
   */
  private $id;

  /**
   * @ORM\Column(type="string", length=190, unique=true)
   * @var string|null
   */
  private $name;

  /**
   * @ORM\ManyToMany(targetEntity=User::class, inversedBy="groups")
   * @var \Doctrine\Common\Collections\Collection|ArrayCollection|User[]
   */
  private $users;

  public function __construct()
  {
    $this->id = \Ramsey\Uuid\Uuid::uuid4();
    $this->users = new ArrayCollection();
  }

  public function getId(): \Ramsey\Uuid\UuidInterface
  {
    return $this->id;
  }

  public function getName(): ?string
  {
    return $this->name;
  }

  public function setName(string $name): self
  {
    $this->name = $name;

    return $this;
  }

  /**
   * @return Collection|User[]
   */
  public function getUsers(): Collection
  {
    return $this->users;
  }

  public function addUser(User $user): self
  {
    if (!$this->users->contains($user)) {
      $this->users[] = $user;
    }

    return $this;
  }

  public function removeUser(User $user): self
  {
    $this->users->removeElement($user);

    return $this;
  }
}
