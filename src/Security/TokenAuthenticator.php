<?php
namespace App\Security;

use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Exception;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\TokenState;
use App\Service\JWTService;
use App\Exception\ApiException;
use App\Entity\User;

final class TokenAuthenticator extends AbstractGuardAuthenticator
{
  /**
   * @var JWTService
   */
  private $jwtService;
  /**
   * @var array<string, string>
   */
  private const DATA = [
    // you might translate this message
    'message' => 'Authentication Required'
  ];

  private const AUTH_HEADER = 'authorization';

  public function __construct(JWTService $jwtService)
  {
    $this->jwtService = $jwtService;
  }

  /**
   * Called on every request to decide if this authenticator should be
   * used for the request. Returning `false` will cause this authenticator
   * to be skipped.
   */
  public function supports(Request $request): bool
  {
    $supports = $request->headers->has(self::AUTH_HEADER);
    return $supports;
  }

  /**
   * Called on every request. Return whatever token$token you want to
   * be passed to getUser() as $token.
   */
  public function getCredentials(Request $request): ?string
  {
    return $request->headers->get(self::AUTH_HEADER);
  }

  public function getUser(
    $token,
    UserProviderInterface $userProvider
  ): ?UserInterface {
    if (null === $token) {
      // The token header was empty, authentication fails with HTTP Status
      // Code 401 "Unauthorized"
      return null;
    }

    try {
      $jwt = $this->jwtService->decode($token);
      // If this returns a user, checkCredentials() is called next
      return $userProvider->loadUserByUsername($jwt->email);
    } catch (Exception $exception) {
      throw new ApiException($exception, $exception->getMessage());
    }
  }

  public function checkCredentials($token, UserInterface $user): bool
  {
    // getUser returned user, so token is actually valid
    return true;
  }

  /**
   * @return null
   */
  public function onAuthenticationSuccess(
    Request $request,
    TokenInterface $token,
    $providerKey
  ) {
    // on success, let the request continue
    return null;
  }

  public function onAuthenticationFailure(
    Request $request,
    AuthenticationException $exception
  ): void {
    throw new ApiException(
      null,
      'Authentication error',
      $exception->getMessage(),
      107,
      Response::HTTP_UNAUTHORIZED
    );
  }

  /**
   * Called when authentication is needed, but it's not sent
   */
  public function start(
    Request $request,
    AuthenticationException $authException = null
  ): JsonResponse {
    return new JsonResponse(self::DATA, Response::HTTP_UNAUTHORIZED);
  }

  public function supportsRememberMe(): bool
  {
    return false;
  }
}
