<?php
namespace App\Security;

use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Intl\Data\Bundle\Writer\JsonBundleWriter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;
use LogicException;
use Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityManager;
use DateTime;
use App\Utils\Uuid;
use App\Utils\PreconfiguredComponents;
use App\Service\UserService;
use App\Service\TokenState;
use App\Service\MySerializer;
use App\Service\JWTService;
use App\Exception\ApiException;
use App\Exception\ApiAuthorizationException;
use App\Entity\User;
use App\Entity\RefreshSession;

final class EmailPasswordAuthenticator
  extends AbstractGuardAuthenticator
  implements PasswordAuthenticatedInterface
{
  /**
   * @var string
   */
  public const LOGIN_ROUTE = 'api_login';
  /** @var User $user */
  private $user;

  /**
   * @var UserPasswordEncoderInterface
   */
  private $passwordEncoder;
  /**
   * @var EntityManagerInterface
   */
  private $em;
  /**
   * @var JWTService
   */
  private $jwtService;
  /**
   * @var MySerializer
   */
  private $normalizer;
  /**
   * @var string
   */
  private const EMAIL = 'email';
  /**
   * @var string
   */
  private const PASSWORD = 'password';
  /**
   * @var string
   */
  private const FINGERPRINT = 'fingerprint';

  public function __construct(
    UserPasswordEncoderInterface $passwordEncoder,
    EntityManagerInterface $em,
    JWTService $jwtService,
    MySerializer $normalizer
  ) {
    $this->passwordEncoder = $passwordEncoder;
    $this->em = $em;
    $this->jwtService = $jwtService;
    $this->normalizer = $normalizer;
  }

  // used for api registration
  public function setUser(User $user): void
  {
    $this->user = $user;
  }

  /**
   * Called on every request to decide if this authenticator should be
   * used for the request. Returning `false` will cause this authenticator
   * to be skipped.
   */
  public function supports(Request $request): bool
  {
    return self::LOGIN_ROUTE === $request->attributes->get('_route') &&
      $request->isMethod('POST');
  }

  /**
   * Called on every request. Return whatever credentials$ you want to
   * be passed to getUser() as $credentials.
   * @return array<string, mixed>
   */
  public function getCredentials(Request $request): array
  {
    return [
      self::EMAIL => $request->request->get(self::EMAIL),
      self::PASSWORD => $request->request->get(self::PASSWORD),
      self::FINGERPRINT => $request->request->get(self::FINGERPRINT)
    ];
  }

  public function getUser(
    $credentials,
    UserProviderInterface $userProvider
  ): \App\Entity\User {
    $email = $credentials[self::EMAIL];
    $this->user = $this->em
      ->getRepository(User::class)
      ->findOneBy([self::EMAIL => $email]);

    if ($this->user === null) {
      // fail authentication with a custom error
      throw new ApiAuthorizationException(
        'Authentication failed',
        [
          self::EMAIL => \sprintf(
            "Пользователь с email '%s' не зарегистрирован.",
            $email
          )
        ],
        107,
        Response::HTTP_UNAUTHORIZED
      );
    }

    return $this->user;
  }

  public function checkCredentials($credentials, UserInterface $user): bool
  {
    if ($credentials[self::FINGERPRINT] == null) {
      throw new ApiException(
        null,
        'Authentication failed',
        'field "fingerprint" is required',
        -1,
        Response::HTTP_BAD_REQUEST
      );
    }
    if (
      $this->passwordEncoder->isPasswordValid(
        $user,
        $credentials[self::PASSWORD] ?? ''
      ) == false
    ) {
      throw new ApiAuthorizationException(
        'Authentication failed',
        [self::PASSWORD => 'Неверный пароль'],
        107,
        Response::HTTP_UNAUTHORIZED
      );
    }
    return true;
  }

  public function onAuthenticationSuccess(
    Request $request,
    TokenInterface $credentials,
    $providerKey
  ): JsonResponse {
    if ($this->user === null) {
      throw new LogicException(
        'User must be present at this step. You can call setUser.'
      );
    }

    $fingerprint = $this->getCredentials($request)[self::FINGERPRINT];
    $tokens = $this->jwtService->createAccessAndRefreshTokens(
      $this->user,
      $fingerprint
    );

    $json = [];
    $json['tokens']['access_token'] = $tokens['access_token'];
    $json['tokens']['refresh_token'] = $tokens['refresh_token'];
    $json['user'] = $this->normalizer->normalize($this->user);
    return new JsonResponse($json);
  }

  /**
   * Used to upgrade (rehash) the user's password automatically over time.
   */
  public function getPassword($credentials): ?string
  {
    return $credentials[self::PASSWORD];
  }

  public function onAuthenticationFailure(
    Request $request,
    AuthenticationException $exception
  ): void {
    throw $exception;
  }

  /**
   * Called when authentication is needed, but it's not sent
   */
  public function start(
    Request $request,
    AuthenticationException $authException = null
  ): void {
    throw new ApiException(
      null,
      'Authentication required',
      "Authorization header is needed, but it's not sent",
      105,
      Response::HTTP_UNAUTHORIZED
    );
  }

  public function supportsRememberMe(): bool
  {
    return false;
  }
}
