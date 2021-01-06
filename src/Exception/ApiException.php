<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Used to store api error details, which are directly converted to response
 */
class ApiException extends \Exception implements
  HttpExceptionInterface,
  \JsonSerializable
{
  /**
   * @var string
   */
  protected $title;
  /**
   * @var string
   */
  protected $detail;
  /**
   * @var int
   */
  protected $statusCode;
  /**
   * @var string
   */
  protected $runtimeType = 'default';
  /**
   * @var mixed[]
   */
  protected $headers = [];

  public function __construct(
    \Exception $previous = null,
    string $title = '',
    string $detail = '',
    int $code = 0,
    int $statusCode = 500,
    array $headers = []
  ) {
    $this->title = $title;
    $this->statusCode = $statusCode;
    $this->headers = $headers;
    $this->detail = $detail;
    parent::__construct($title, $code, $previous);
  }

  // custom string representation of object
  public function __toString()
  {
    return __CLASS__ . \sprintf(': [%s]: %s %s', $this->code, $this->title, $this->message, PHP_EOL);
  }

  /**
   * @return array<string, mixed>
   */
  public function jsonSerialize(): array
  {
    return [
      'runtimeType' => $this->runtimeType,
      'title' => $this->title,
      'detail' => $this->detail,
      'errno' => $this->code
    ];
  }

  /**
   * Get the value of title
   */
  public function getTitle(): string
  {
    return $this->title;
  }

  /**
   * Get the value of headers
   * @return mixed[]
   */
  public function getHeaders(): array
  {
    return $this->headers;
  }

  /**
   * Get the value of statusCode
   */
  public function getStatusCode(): int
  {
    return $this->statusCode;
  }

  /**
   * Get the value of detail
   */
  public function getDetail(): string
  {
    return $this->detail;
  }
}
