<?php

namespace App\Utils;

final class EntityHelper
{
  /**
   * Used to determine if given object is an array of given entity class
   *
   * @param object|array $object
   * @param string $className
   */
  public static function isArrayOfType($object, string $className): bool
  {
    // no need to walk through all array,
    // as all of it's elements are always the same entity type
    // reset return first element of an array or false
    return \is_array($object) && \is_a(reset($object), $className);
  }

  /**
   * Gets private attribute value
   * !!!FOR TESTS ONLY!!!
   *
   * @param object $obj
   * @param string $attribute
   * @return mixed
   */
  public static function getPrivate(
    object $obj,
    string $attribute
  ): \ReflectionProperty {
    $reflection = new \ReflectionClass($obj);
    return $reflection->getProperty($attribute);
  }

  /**
   * Sets private attribute value
   * !!!FOR TESTS ONLY!!!
   *
   * @param object $obj
   * @param string $attribute
   * @param mixed $value
   * @return mixed
   */
  public static function setPrivate(
    object $obj,
    string $attribute,
    $value
  ): \ReflectionProperty {
    $reflection = new \ReflectionClass($obj);
    $property = $reflection->getProperty($attribute);
    $property->setAccessible(true);
    $property->setValue($obj, $value);
    return $property;
  }
}
