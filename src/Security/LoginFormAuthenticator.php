<?php

namespace App\Security;

use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

final class LoginFormAuthenticator extends AbstractFormLoginAuthenticator implements
  PasswordAuthenticatedInterface
{
  use TargetPathTrait;

  /**
   * @var string
   */
  public const LOGIN_ROUTE = 'login';

  /**
   * @var EntityManagerInterface
   */
  private $entityManager;
  /**
   * @var UrlGeneratorInterface
   */
  private $urlGenerator;
  /**
   * @var CsrfTokenManagerInterface
   */
  private $csrfTokenManager;
  /**
   * @var UserPasswordEncoderInterface
   */
  private $passwordEncoder;
  /**
   * @var string
   */
  private const EMAIL = 'email';
  /**
   * @var string
   */
  private const PASSWORD = 'password';

  public function __construct(
    EntityManagerInterface $entityManager,
    UrlGeneratorInterface $urlGenerator,
    CsrfTokenManagerInterface $csrfTokenManager,
    UserPasswordEncoderInterface $passwordEncoder
  ) {
    $this->entityManager = $entityManager;
    $this->urlGenerator = $urlGenerator;
    $this->csrfTokenManager = $csrfTokenManager;
    $this->passwordEncoder = $passwordEncoder;
  }

  public function supports(Request $request): bool
  {
    return self::LOGIN_ROUTE === $request->attributes->get('_route') &&
      $request->isMethod('POST');
  }

  /**
   * @return array<string, mixed>
   */
  public function getCredentials(Request $request): array
  {
    $credentials = [
      self::EMAIL => $request->request->get(self::EMAIL),
      self::PASSWORD => $request->request->get(self::PASSWORD),
      'csrf_token' => $request->request->get('_csrf_token')
    ];
    $request->getSession()->set(Security::LAST_USERNAME, $credentials[self::EMAIL]);

    return $credentials;
  }

  public function getUser($credentials, UserProviderInterface $userProvider): User
  {
    $token = new CsrfToken('authenticate', $credentials['csrf_token']);
    if (!$this->csrfTokenManager->isTokenValid($token)) {
      throw new InvalidCsrfTokenException();
    }

    $email = $credentials[self::EMAIL];
    $user = $this->entityManager
      ->getRepository(User::class)
      ->findOneBy([self::EMAIL => $email]);

    if ($user === null) {
      // fail authentication with a custom error
      throw new CustomUserMessageAuthenticationException(
        \sprintf("Пользователь с данным email: '%s' не зарегистрирован.", $email)
      );
    }

    return $user;
  }

  public function checkCredentials($credentials, UserInterface $user): bool
  {
    return $this->passwordEncoder->isPasswordValid(
      $user,
      $credentials[self::PASSWORD]
    );
  }

  /**
   * Used to upgrade (rehash) the user's password automatically over time.
   */
  public function getPassword($credentials): ?string
  {
    return $credentials[self::PASSWORD];
  }

  public function onAuthenticationSuccess(
    Request $request,
    TokenInterface $token,
    $providerKey
  ): RedirectResponse {
    if (
      $targetPath = $this->getTargetPath($request->getSession(), $providerKey)
    ) {
      return new RedirectResponse($targetPath);
    }

    return new RedirectResponse($this->urlGenerator->generate('index'));
  }

  protected function getLoginUrl(): string
  {
    return $this->urlGenerator->generate(self::LOGIN_ROUTE);
  }
}
