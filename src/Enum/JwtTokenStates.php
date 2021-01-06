<?php

namespace App\Enum;

final class JwtTokenStates extends BasicEnum
{
  /**
   * @var int
   */
  const Valid = 0;
  /**
   * @var int
   */
  const Invalid = 1;
  /**
   * @var int
   */
  const Expired = 2;
  /**
   * @var int
   */
  const Violated = 3;
  /**
   * @var int
   */
  const Invalidated = 4;
}
