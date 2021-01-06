<?php

namespace App\Exception;

final class ApiAuthorizationException extends ApiException
{
  public function __construct(
    $title = '',
    $detail = '',
    $code = 0,
    $statusCode = 401,
    $headers = [],
    \Exception $previous = null
  ) {
    parent::__construct(
      $title,
      $detail,
      $code,
      $statusCode,
      $headers,
      $previous
    );
  }
  /**
   * @var string
   */
  protected $runtimeType = 'authorization';
}
