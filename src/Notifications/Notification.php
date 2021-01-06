<?php

namespace App\Notifications;

final class Notification
{
  /**
   * @var string|null
   */
  private $title;
  /**
   * @var string|null
   */
  private $body;
  /**
   * @var string|null
   */
  private $image;

  public function getTitle(): ?string
  {
    return $this->title;
  }

  public function setTitle(string $title): self
  {
    $this->title = $title;

    return $this;
  }

  public function getBody(): ?string
  {
    return $this->body;
  }

  public function setBody(string $body): self
  {
    $this->body = $body;

    return $this;
  }

  public function getImage(): ?string
  {
    return $this->image;
  }

  public function setImage(string $image): self
  {
    $this->image = $image;

    return $this;
  }
}
