<?php
namespace App\Enum;

use ReflectionClass;
use App\Exception\NotSupportedException;

abstract class BasicEnum
{
  /**
   * @var mixed[]|array<mixed, array<string, mixed>>|null
   */
  private static $constCacheArray;

  final private function __construct()
  {
    throw new NotSupportedException();
  }

  final private function __clone()
  {
    throw new NotSupportedException();
  }
  public static function isValidName($name, $strict = false): bool
  {
    $constants = self::getConstants();

    if ($strict) {
      return \array_key_exists($name, $constants);
    }

    $keys = \array_map('strtolower', \array_keys($constants));
    return \in_array(\strtolower($name), $keys);
  }
  public static function isValidValue($value, $strict = true): bool
  {
    $values = \array_values(self::getConstants());
    return \in_array($value, $values, $strict);
  }

  /**
   * @return array<string, mixed>
   */
  final public static function toArray(): array
  {
    return (new ReflectionClass(static::class))->getConstants();
  }

  final public static function isValid($value): bool
  {
    return \in_array($value, static::toArray());
  }
  private static function getConstants()
  {
    if (self::$constCacheArray == null) {
      self::$constCacheArray = [];
    }
    $calledClass = \get_called_class();
    if (!\array_key_exists($calledClass, self::$constCacheArray)) {
      $reflect = new ReflectionClass($calledClass);
      self::$constCacheArray[$calledClass] = $reflect->getConstants();
    }
    return self::$constCacheArray[$calledClass];
  }
}
