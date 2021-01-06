<?php

namespace App\Normalizer;

use App\Entity\User;

interface UserAwareNormalizerInterface
{
  public function setUser(?User $user);
}
