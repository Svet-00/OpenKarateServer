<?php

namespace App\Normalizer;

use App\Entity\User;

trait UserAwareTrait
{
  /** @var User */
  private $user;

  public function setUser(?User $user)
  {
    $this->user = $user;
  }
}
