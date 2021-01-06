<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

final class EmailVerifier
{
  /**
   * @var VerifyEmailHelperInterface
   */
  private $verifyEmailHelper;
  /**
   * @var MailerInterface
   */
  private $mailer;
  /**
   * @var EntityManagerInterface
   */
  private $entityManager;

  public function __construct(
    VerifyEmailHelperInterface $helper,
    MailerInterface $mailer,
    EntityManagerInterface $manager
  ) {
    $this->verifyEmailHelper = $helper;
    $this->mailer = $mailer;
    $this->entityManager = $manager;
  }

  public function sendEmailConfirmation(
    string $verifyEmailRouteName,
    User $user,
    TemplatedEmail $email
  ): void {
    $signatureComponents = $this->verifyEmailHelper->generateSignature(
      $verifyEmailRouteName,
      $user->getId(),
      $user->getEmail()
    );

    $context = $email->getContext();
    $context['signedUrl'] = $signatureComponents->getSignedUrl();
    $context['expiresAt'] = $signatureComponents->getExpiresAt();

    $email->context($context);

    $this->mailer->send($email);
  }

  /**
   * @throws VerifyEmailExceptionInterface
   */
  public function handleEmailConfirmation(Request $request, User $user): void
  {
    $this->verifyEmailHelper->validateEmailConfirmation(
      $request->getUri(),
      $user->getId(),
      $user->getEmail()
    );

    $user->setIsVerified(true);

    $this->entityManager->persist($user);
    $this->entityManager->flush();
  }
}
