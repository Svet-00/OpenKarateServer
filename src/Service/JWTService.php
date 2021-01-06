<?php
namespace App\Service;

use Ramsey\Uuid\Uuid;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Exception;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use App\Enum\JwtTokenStates;
use App\Entity\User;
use App\Entity\RefreshSession;

final class JWTService
{
  /**
   * @var EntityManagerInterface
   */
  private $em;
  /**
   * @var resource|bool
   */
  private $privateKey;
  /**
   * @var null|RefreshSession
   */
  private $refreshSession;
  /**
   * @var User|null
   */
  private $user;
  /**
   * @var string
   */
  private const JTI = 'jti';
  /**
   * @var string
   */
  private const IAT = 'iat';
  /**
   * @var string
   */
  private const EXP = 'exp';
  /**
   * @var string
   */
  private const REFRESH_TOKEN_LIFE_TIME = '60 days';

  public function __construct(string $privateKey, EntityManagerInterface $em)
  {
    $this->privateKey = $privateKey;
    $this->em = $em;
  }

  /**
   * @return array<string, string>
   */
  public function createAccessAndRefreshTokens(
    User $user,
    string $fingerprint
  ): array {
    $this->user = $user;
    $refreshTokenId = Uuid::uuid4();

    $refreshSession = new RefreshSession();
    $refreshSession->setFingerprint($fingerprint);
    $refreshSession->setRefreshToken($refreshTokenId);
    $refreshSession->setExpiresIn(
      (new DateTime())->modify('+' . self::REFRESH_TOKEN_LIFE_TIME)
    );
    $refreshSession->setUser($this->user);

    // keep only last refresh session for each device
    $deprecatedSessions = $this->em
      ->getRepository(RefreshSession::class)
      ->findBy(['fingerprint' => $refreshSession->getFingerprint()]);
    foreach ($deprecatedSessions as $ds) {
      $this->em->remove($ds);
    }

    $this->em->persist($refreshSession);

    $this->em->flush();

    $refreshTokenPayload = [
      'type' => 'refresh',
      self::JTI => $refreshTokenId
    ];
    $refreshToken = $this->create(
      $refreshTokenPayload,
      self::REFRESH_TOKEN_LIFE_TIME
    );

    $accessTokenPayload = [
      'type' => 'access',
      'email' => $this->user->getEmail()
    ];
    $accessToken = $this->create($accessTokenPayload, '30 minutes');

    return ['access_token' => $accessToken, 'refresh_token' => $refreshToken];
  }

  /**
   * @param object $refreshToken
   */
  public function verifyRefreshToken(
    object $refreshToken,
    string $fingerprint
  ): int {
    $refreshSession = $this->getRefreshSession($refreshToken->jti);
    if ($refreshSession == null) {
      return JwtTokenStates::Invalidated;
    }

    // someone tries to get access token from client that did not ricieved that token
    if ($fingerprint != $refreshSession->getFingerprint()) {
      $sessions = $this->getUserFromRefreshToken(
        $refreshToken
      )->getRefreshSessions();
      foreach ($sessions as $session) {
        $this->em->remove($session);
      }
      $this->em->flush();
      return JwtTokenStates::Violated;
    }

    return JwtTokenStates::Valid;
  }

  /**
   * @param object $token
   */
  public function invalidateRefreshToken(object $token): void
  {
    $refreshSession = $this->getRefreshSession($token->jti);
    $this->em->remove($refreshSession);
    $this->em->flush();
  }

  /**
   * @return mixed|null|User
   */
  public function getUserFromRefreshToken($refreshToken): ?User
  {
    if ($this->user != null) {
      return $this->user;
    }
    $session = $this->getRefreshSession($refreshToken->jti);
    if ($session != null) {
      return $this->user = $session->getUser();
    }
    return null;
  }

  public function create(array $payload, string $lifeTime): string
  {
    $now = new DateTime();
    $iat = $now->getTimestamp();
    $exp = $now->modify('+' . $lifeTime)->getTimestamp();

    $payload[self::JTI] = isset($payload[self::JTI])
      ? $payload[self::JTI]
      : Uuid::uuid4();
    $payload[self::IAT] = isset($payload[self::IAT])
      ? $payload[self::IAT]
      : $iat;
    $payload[self::EXP] = isset($payload[self::EXP])
      ? $payload[self::EXP]
      : $exp;

    return JWT::encode($payload, $this->privateKey, 'HS256');
  }

  public function decode(string $jwt): object
  {
    return JWT::decode($jwt, $this->privateKey, ['HS256']);
  }

  /**
   * @param string $jwt
   * @return integer represents one of TokenState values
   */
  public function verify(string $jwt, bool $ignoreExpired = false): int
  {
    try {
      $this->decode($jwt);
    } catch (ExpiredException $expiredException) {
      if ($ignoreExpired == false) {
        return JwtTokenStates::Expired;
      }
    } catch (Exception $exception) {
      return JwtTokenStates::Invalid;
    }
    return JwtTokenStates::Valid;
  }

  private function getRefreshSession(string $refreshTokenId): ?RefreshSession
  {
    return $this->refreshSession ??
      ($this->refreshSession = $this->em
        ->getRepository(RefreshSession::class)
        ->findOneBy(['refreshToken' => $refreshTokenId]));
  }
}
