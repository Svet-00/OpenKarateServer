<?php

namespace App\Notifications;

final class NotificationTopic
{
  /**
   * @var string|null
   */
  private $stringRepresentation;
  /**
   * @var string|null
   */
  private $description;
  /**
   * @var bool|null
   */
  private $important;

  public function getStringRepresentation(): ?string
  {
    return $this->stringRepresentation;
  }

  public function setStringRepresentation(string $stringRepresentation): self
  {
    $this->stringRepresentation = $stringRepresentation;

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

  public function getImportant(): ?bool
  {
    return $this->important;
  }

  public function setImportant(bool $important): self
  {
    $this->important = $important;

    return $this;
  }
}
