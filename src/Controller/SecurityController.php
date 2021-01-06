<?php

namespace App\Controller;

use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Annotations as OA;
use Firebase\JWT\JWT;
use Exception;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\JWTService;
use App\Exception\ApiException;
use App\Enum\JwtTokenStates;
use App\Entity\User;
use App\Entity\RefreshSession;

final class SecurityController extends AbstractController
{
  /**
   * @var JWTService
   */
  private $jwtService;
  /**
   * @var string
   */
  private const REFRESH_TOKEN = 'refresh_token';
  /**
   * @var string
   */
  private const ERROR_TITLE = 'Token refresh error';

  public function __construct(JWTService $jwtService)
  {
    $this->jwtService = $jwtService;
  }

  /**
   * @Route("/login", name="login")
   */
  public function loginSite(AuthenticationUtils $authenticationUtils): Response
  {
    if ($this->getUser() !== null) {
      return $this->redirectToRoute('profile');
    }

    // get the login error if there is one
    $error = $authenticationUtils->getLastAuthenticationError();

    // last username entered by the user
    $lastUsername = $authenticationUtils->getLastUsername();

    return $this->render('security/login.twig', [
      'last_username' => $lastUsername,
      'error' => $error
    ]);
  }

  /**
   * @OA\Tag(name="Authorization")
   * @Route("/api/v1.0/auth", name="api_login", methods={"POST"})
   */
  public function loginApi(): void
  {
    // force EmailPasswordAuthenticator to be called
    $this->denyAccessUnlessGranted('ROLE_USER');
  }

  /**
   * @OA\Tag(name="Authorization")
   * @Route("/api/v1.0/auth/refresh-tokens", name="api_refresh_tokens", methods={"POST"})
   */
  public function refreshTokens(Request $request): JsonResponse
  {
    $fingerprint = $request->request->get('fingerprint');
    $refreshToken = $request->request->get(self::REFRESH_TOKEN);
    $errorStatusCode = Response::HTTP_UNAUTHORIZED;

    if ($fingerprint == null || $refreshToken == null) {
      throw new ApiException(
        null,
        self::ERROR_TITLE,
        '"fingerprint" and "refresh_token" parameters are required',
        -1,
        Response::HTTP_BAD_REQUEST
      );
    }

    $refreshTokenVerificationResult = $this->jwtService->verify($refreshToken);
    if ($refreshTokenVerificationResult != JwtTokenStates::Valid) {
      if ($refreshTokenVerificationResult == JwtTokenStates::Expired) {
        throw new ApiException(
          null,
          self::ERROR_TITLE,
          'Refresh token expired',
          101,
          $errorStatusCode
        );
      }
      throw new ApiException(
        null,
        self::ERROR_TITLE,
        'Invalid refresh token',
        102,
        $errorStatusCode
      );
    }

    $refreshToken = $this->jwtService->decode($refreshToken);

    $verificationResult = $this->jwtService->verifyRefreshToken(
      $refreshToken,
      $fingerprint
    );
    if ($verificationResult == JwtTokenStates::Invalidated) {
        // refresh token was invalidated
        throw new ApiException(
          null,
          self::ERROR_TITLE,
          'Refresh token invalidated',
          103,
          $errorStatusCode
        );
    }

    if ($verificationResult == JwtTokenStates::Violated) {
        // refresh token is violated
        throw new ApiException(
          null,
          self::ERROR_TITLE,
          'Refresh token is used with wrong client',
          104,
          $errorStatusCode
        );
    }

    $this->jwtService->invalidateRefreshToken($refreshToken);

    $user = $this->jwtService->getUserFromRefreshToken($refreshToken);
    $newTokens = $this->jwtService->createAccessAndRefreshTokens(
      $user,
      $fingerprint
    );
    $json = [];
    $json['access_token'] = $newTokens['access_token'];
    $json[self::REFRESH_TOKEN] = $newTokens[self::REFRESH_TOKEN];
    return new JsonResponse($json);
  }

  /**
   * @Route("/logout", name="logout")
   */
  public function logout(): void
  {
    // This method can be blank - it will be intercepted by the logout key on your firewall
  }
}
