<?php
declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use OpenApi\Annotations as OA;
use LogicException;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\UserRepository;
use App\Enum\AvatarFormats;

/**
 * @ORM\Cache("NONSTRICT_READ_WRITE")
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="Аккаунт с таким Email уже существует")
 */
class User implements UserInterface
{
  /**
   * @var string The hashed password
   * @ORM\Column(type="string")
   * @Assert\NotBlank(
   * message = "Это поле не может быть пустым",
   * normalizer = "trim"
   * )
   */
  private $password;

  /**
   * @var string
   *
   * @ORM\Column(name="name", type="string", length=15, nullable=false)
   * @Assert\NotBlank(
   * message = "Это поле не может быть пустым",
   * normalizer = "trim"
   * )
   * @Groups("api")
   */
  private $name;

  /**
   * @var string
   *
   * @ORM\Column(name="surname", type="string", length=20, nullable=false)
   * @Assert\NotBlank(
   * message = "Это поле не может быть пустым",
   * normalizer = "trim"
   * )
   * @Groups("api")
   */
  private $surname;
  /**
   * @ORM\Column(type="boolean")
   * @var bool
   */
  private $isVerified = false;
  /**
   * @ORM\Column(type="json")
   * @var mixed[]
   */
  private $roles = [];

  /**
   * @var string|null
   *
   * @ORM\Column(name="patronymic", type="string", length=20, nullable=true)
   * @Groups("api")
   */
  private $patronymic;

  /**
   * @var \DateTime|null
   *
   * @ORM\Column(name="birthday", type="date", nullable=true)
   * @Groups("api")
   */
  private $birthday;
  /**
   * @ORM\Id()
   * @ORM\Column(type="uuid_binary")
   * @Groups("api")
   * @var \Ramsey\Uuid\UuidInterface
   */
  private $id;
  /**
   * @ORM\Column(type="string", length=180, unique=true)
   * @Assert\NotBlank(
   * message = "Это поле не может быть пустым",
   * normalizer = "trim"
   * )
   * @Assert\Email(
   *   message = "{{ value }} не является правильным адресом электронной почты.",
   *   mode = "html5"
   * )
   * @Groups("api")
   * @var string|null
   */
  private $email;

  /**
   * @ORM\Column(type="integer", nullable=false)
   * @Groups("api")
   * @var int|null
   */
  private $level = 0;

  /**
   * @ORM\Column(type="string", length=255, nullable=true)
   *
   * @Assert\Length(
   * max = 255,
   * maxMessage = "Короткое описание не может быть длиннее {{ limit }} символов",
   * allowEmptyString = true
   * )
   * @Groups("api")
   * @var string|null
   */
  private $shortDescription;

  /**
   * @ORM\Column(type="text", nullable=true)
   * @Groups("api")
   * @var string|null
   */
  private $longDescription;

  /**
   * @ORM\Column(type="string", length=255, nullable=true)
   *  'uniqueId_{{ format }}.ext'
   * @var string|null
   */
  private $avatarFilenameTemplate;

  /**
   * @ORM\Column(type="datetime")
   * @var \DateTime|\DateTimeInterface|null
   */
  private $registeredAt;

  /**
   * @ORM\OneToMany(targetEntity=RefreshSession::class, mappedBy="user", orphanRemoval=true)
   * @var \Doctrine\Common\Collections\Collection|ArrayCollection|RefreshSession[]
   */
  private $refreshSessions;

  /**
   * @ORM\ManyToMany(targetEntity=Group::class, mappedBy="users")
   * @Groups("api")
   * @var \Doctrine\Common\Collections\Collection|ArrayCollection|Group[]
   */
  private $groups;

  /**
   * @ORM\ManyToMany(targetEntity=Event::class, mappedBy="participants")
   * @Groups("api")
   * @var \Doctrine\Common\Collections\Collection|ArrayCollection|Event[]
   */
  private $events;

  /**
   * @ORM\ManyToMany(targetEntity=Gym::class)
   * @ORM\JoinTable("user_favorite_gyms")
   * @var ArrayCollection|Gym[]
   */
  private $favoriteGyms;
  /**
   * @var string
   */
  private const ROLE_ADMIN = 'ROLE_ADMIN';
  /**
   * @var string
   */
  private const ROLE_TRAINER = 'ROLE_TRAINER';

  public function __construct()
  {
    $this->id = \Ramsey\Uuid\Uuid::uuid4();
    if ($this->registeredAt == null) {
      $this->registeredAt = new \DateTime();
    }
    $this->refreshSessions = new ArrayCollection();
    $this->groups = new ArrayCollection();
    $this->events = new ArrayCollection();
    $this->favoriteGyms = new ArrayCollection();
  }

  // public function isEqualTo(UserInterface $other): bool
  // {
  //   return $this->getPassword() == $other->getPassword()  ;
  // }

  public function getId(): \Ramsey\Uuid\UuidInterface
  {
    return $this->id;
  }

  public function getEmail(): ?string
  {
    return $this->email;
  }

  public function setEmail(string $email): self
  {
    $this->email = $email;

    return $this;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function setName(string $name): self
  {
    $this->name = $name;

    return $this;
  }

  public function getSurname(): string
  {
    return $this->surname;
  }

  public function setSurname(string $surname): self
  {
    $this->surname = $surname;

    return $this;
  }

  public function getPatronymic(): ?string
  {
    return $this->patronymic;
  }

  public function setPatronymic(?string $patronymic): self
  {
    $this->patronymic = $patronymic;

    return $this;
  }

  public function getBirthday(): ?\DateTimeInterface
  {
    return $this->birthday;
  }

  public function setBirthday(?\DateTimeInterface $birthday): self
  {
    $this->birthday = $birthday;

    return $this;
  }

  public function getLevel(): ?int
  {
    return $this->level;
  }

  public function setLevel(?int $level): self
  {
    $this->level = $level;

    return $this;
  }

  public function getShortDescription(): ?string
  {
    return $this->shortDescription;
  }

  public function setShortDescription(?string $shortDescription): self
  {
    $this->shortDescription = $shortDescription;

    return $this;
  }

  public function getLongDescription(): ?string
  {
    return $this->longDescription;
  }

  public function setLongDescription(?string $longDescription): self
  {
    $this->longDescription = $longDescription;

    return $this;
  }

  /**
   * @return boolean
   * @Groups("api")
   */
  public function isAdmin(): bool
  {
    return \in_array(self::ROLE_ADMIN, $this->getRoles());
  }

  public function setIsAdmin(bool $value): void
  {
    if ($value) {
      $this->addRole(self::ROLE_ADMIN);
    } else {
      $this->removeRole(self::ROLE_ADMIN);
    }
  }

  /**
   * @return boolean
   * @Groups("api")
   */
  public function isTrainer(): bool
  {
    return \in_array(self::ROLE_TRAINER, $this->getRoles());
  }

  public function setIsTrainer(bool $value): void
  {
    if ($value) {
      $this->addRole(self::ROLE_TRAINER);
    } else {
      $this->removeRole(self::ROLE_TRAINER);
    }
  }

  /**
   * A visual identifier that represents this user.
   *
   * @see UserInterface
   */
  public function getUsername(): string
  {
    return (string) $this->email;
  }

  /**
   * @see UserInterface
   * @return mixed[]
   */
  public function getRoles(): array
  {
    $roles = $this->roles;
    // guarantee every user at least has ROLE_USER
    $roles[] = 'ROLE_USER';

    return \array_unique($roles);
  }

  /**
   * @param mixed[] $roles
   */
  public function setRoles(array $roles): self
  {
    $this->roles = $roles;

    return $this;
  }

  public function addRole(string $role): self
  {
    $this->setRoles(\array_merge($this->getRoles(), [$role]));
    return $this;
  }

  public function removeRole(string $role): self
  {
    $this->setRoles(\array_diff($this->getRoles(), [$role]));
    return $this;
  }

  /**
   * @see UserInterface
   */
  public function getPassword(): string
  {
    return (string) $this->password;
  }

  public function setPassword(string $password): self
  {
    $this->password = $password;

    return $this;
  }

  /**
   * @see UserInterface
   */
  public function getSalt(): void
  {
    // not needed when using the "bcrypt" algorithm in security.yaml
  }

  /**
   * @see UserInterface
   */
  public function eraseCredentials(): void
  {
    // If you store any temporary, sensitive data on the user, clear it here
    // $this->plainPassword = null;
  }

  public function isVerified(): bool
  {
    return $this->isVerified;
  }

  public function setIsVerified(bool $isVerified): self
  {
    $this->isVerified = $isVerified;

    return $this;
  }

  /**
   * @Groups("api")
   * @OA\Property(type="array", @OA\Items(type="string"))
   */
  public function hasAvatar(): bool
  {
    return $this->avatarFilenameTemplate != '' &&
      $this->avatarFilenameTemplate != null;
  }

  public function getAvatarFilename(string $format): string
  {
    if (AvatarFormats::isValidValue($format) == false) {
      throw new LogicException('Incorrect image format');
    }

    $template = $this->hasAvatar()
      ? $this->avatarFilenameTemplate
      : 'no_avatar_{{ format }}.png';

    return \str_replace('{{ format }}', $format, $template);
  }

  public function getAvatarFilenameTemplate(): ?string
  {
    return $this->avatarFilenameTemplate;
  }

  public function setAvatarFilenameTemplate(
    ?string $avatarFilenameTemplate
  ): self {
    $this->avatarFilenameTemplate = $avatarFilenameTemplate;

    return $this;
  }

  public function getRegisteredAt(): ?\DateTimeInterface
  {
    return $this->registeredAt;
  }

  public function setRegisteredAt(\DateTimeInterface $registeredAt): self
  {
    $this->registeredAt = $registeredAt;

    return $this;
  }

  /**
   * @return Collection|RefreshSession[]
   */
  public function getRefreshSessions(): Collection
  {
    return $this->refreshSessions;
  }

  public function addRefreshSession(RefreshSession $refreshSession): self
  {
    if (!$this->refreshSessions->contains($refreshSession)) {
      $this->refreshSessions[] = $refreshSession;
      $refreshSession->setUser($this);
    }

    return $this;
  }

  public function removeRefreshSession(RefreshSession $refreshSession): self
  {
    // set the owning side to null (unless already changed)
    if (
      $this->refreshSessions->removeElement($refreshSession) &&
      $refreshSession->getUser() === $this
    ) {
      $refreshSession->setUser(null);
    }

    return $this;
  }

  /**
   * @return Collection|Group[]
   */
  public function getGroups(): Collection
  {
    return $this->groups;
  }

  public function addGroup(Group $group): self
  {
    if (!$this->groups->contains($group)) {
      $this->groups[] = $group;
      $group->addUser($this);
    }

    return $this;
  }

  public function removeGroup(Group $group): self
  {
    if ($this->groups->removeElement($group)) {
      $group->removeUser($this);
    }

    return $this;
  }

  /**
   * @return Collection|Event[]
   */
  public function getEvents(): Collection
  {
    return $this->events;
  }

  public function addEvent(Event $event): self
  {
    if (!$this->events->contains($event)) {
      $this->events[] = $event;
      $event->addParticipant($this);
    }

    return $this;
  }

  public function removeEvent(Event $event): self
  {
    if ($this->events->removeElement($event)) {
      $event->removeParticipant($this);
    }

    return $this;
  }

  /**
   * @return Collection|Gym[]
   */
  public function getfavoriteGyms(): Collection
  {
    return $this->favoriteGyms;
  }

  public function addfavoriteGym(Gym $favoriteGym): self
  {
    if (!$this->favoriteGyms->contains($favoriteGym)) {
      $this->favoriteGyms[] = $favoriteGym;
    }

    return $this;
  }

  public function removefavoriteGym(Gym $favoriteGym): self
  {
    $this->favoriteGyms->removeElement($favoriteGym);

    return $this;
  }
}
