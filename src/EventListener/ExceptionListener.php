<?php
namespace App\EventListener;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use App\Exception\ApiException;

final class ExceptionListener
{
  private $env;
  /**
   * @var array<int, string>
   */
  private const TITLES = [
    Response::HTTP_INTERNAL_SERVER_ERROR => 'Internal Server Error',
    Response::HTTP_BAD_REQUEST => 'Bad Request',
    Response::HTTP_NOT_FOUND => 'Not Found'
  ];
  public function __construct($env)
  {
    $this->env = $env;
  }

  public function onKernelException(ExceptionEvent $event): void
  {
    // You get the exception object from the received event
    $exception = $event->getThrowable();

    $url = $event->getRequest()->getUri();
    if (\str_contains($url, '/api')) {
      // Customize your response object to display the exception details
      $response = new Response();
      $content = [];

      if ($exception instanceof ApiException) {
        $response->setStatusCode($exception->getStatusCode());
        $content = $exception;
      } else {
        if ($this->env == 'dev') {
          throw $exception;
        }

        $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);

        $fakeException = new ApiException(
          $exception,
          $this->getTitle(Response::HTTP_INTERNAL_SERVER_ERROR),
          '',
          0
        );
        $content = $fakeException;
      }

      $response->setContent(\json_encode($content));
      $response->headers->add([
        'Content-Type' => 'application/json'
      ]);

      $event->setResponse($response);
    }
  }

  private function getTitle(int $errorCode): string
  {
    if (isset(self::TITLES[$errorCode])) {
      return self::TITLES[$errorCode];
    }
    return 'Error';
  }
}
