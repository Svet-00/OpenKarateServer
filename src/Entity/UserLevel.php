<?php

namespace App\Entity;

use App\Exception\FormatException;

final class UserLevel
{
  public const MAX_LEVEL = 21;

  private $level;
  public function __construct(int $level)
  {
    $this->level = $level;
  }

  public function isSportsman(): bool
  {
    return $this->level != 0;
  }

  public function isKyu(): bool
  {
    return $this->isSportsman() && $this->level < 12;
  }

  public function isDan(): bool
  {
    return $this->isSportsman() && !$this->isKyu();
  }

  public function toString(): string
  {
    if (!$this->isSportsman()) {
      // should be 2 words, fromString depends on it
      return 'Не спортсмен';
    }

    return $this->isKyu()
      ? \sprintf('%d Кю', $this->level)
      : \sprintf('%d Дан', $this->level - 11);
  }

  public function toInt(): int
  {
    return $this->level;
  }

  public static function generateChoices(): array
  {
    $res = [];
    for ($i = 0; $i <= UserLevel::MAX_LEVEL; $i++) {
      $lvl = new UserLevel($i);
      $res[$lvl->toString()] = $lvl->toInt();
    }
    return $res;
  }

  public static function fromString(string $level): UserLevel
  {
    [$num, $modifier] = \explode(' ', $level);
    if (
      !$num ||
      $num > self::MAX_LEVEL ||
      !$modifier ||
      !\in_array($modifier, ['Кю', 'Дан', 'спортсмен'])
    ) {
      throw new FormatException('Wrong level format');
    }
    if ($modifier == 'Дан') {
      return new UserLevel($num + 11);
    }

    return new UserLevel($num);
  }
}
