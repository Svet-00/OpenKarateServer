<?php

namespace App\Exception;
/**
 * Used to mark functionality wich is not supported
 */
final class FormatException extends \Exception
{
  public function __construct(
    $message = null,
    $code = 0,
    \Exception $previous = null
  ) {
    // make sure everything is assigned properly
    parent::__construct($message, $code, $previous);
  }

  // custom string representation of object
  public function __toString()
  {
    return __CLASS__ .
      \sprintf(': [%s]: %s', $this->code, $this->message, PHP_EOL);
  }
}
