<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\Dotenv\Dotenv;
use App\Kernel;

require dirname(__DIR__) . '/config/defaults.php';
require dirname(__DIR__) . '/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');

if ($_SERVER['APP_DEBUG']) {
  umask(0000);

  Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? false) {
  Request::setTrustedProxies(
    explode(',', $trustedProxies),
    Request::HEADER_X_FORWARDED_FOR |
      Request::HEADER_X_FORWARDED_PORT |
      Request::HEADER_X_FORWARDED_PROTO
  );
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? false) {
  Request::setTrustedHosts([$trustedHosts]);
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$request = Request::createFromGlobals();

// authorization header is lost somehow
// so we need to manually set it here
if (
  $request->headers->has('Authorization') == false &&
  function_exists('apache_request_headers')
) {
  $all = apache_request_headers();
  if ($token = $all['authorization'] ?? ($all['Authorization'] ?? false)) {
    $request->headers->set('authorization', $token);
  }
}

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
