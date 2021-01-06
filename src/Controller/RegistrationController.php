<?php

namespace App\Controller;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\MySerializer\SerializerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Psr\Log\LoggerInterface;
use OpenApi\Annotations as OA;
use App\Service\MySerializer;
use App\Security\LoginFormAuthenticator;
use App\Security\EmailVerifier;
use App\Security\EmailPasswordAuthenticator;
use App\Form\RegistrationFormType;
use App\Exception\ApiException;
use App\Exception\ApiAuthorizationException;
use App\Entity\User;

final class RegistrationController extends AbstractController
{
  /**
   * @var EmailVerifier
   */
  private $emailVerifier;

  public function __construct(EmailVerifier $emailVerifier)
  {
    $this->emailVerifier = $emailVerifier;
  }

  /**
   * @Route("/register", name="register")
   */
  public function registerSite(
    Request $request,
    UserPasswordEncoderInterface $passwordEncoder,
    GuardAuthenticatorHandler $guardHandler,
    LoginFormAuthenticator $authenticator,
    LoggerInterface $logger,
    MySerializer $serializer
  ): Response {
    $user = new User();
    $user->setPassword('useles data for form to be happy');
    $form = $this->createForm(RegistrationFormType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
      if ($form->isValid()) {
        // encode the plain password
        $user->setPassword(
          $passwordEncoder->encodePassword(
            $user,
            $form->get('plainPassword')->getData()
          )
        );

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $this->sendVerificationEmail($user);

        return $guardHandler->authenticateUserAndHandleSuccess(
          $user,
          $request,
          $authenticator,
          'main' // firewall name in security.yaml
        );
      }
      $errors = $form->getErrors(true);
      $logger->alert(
        'Registration form invalid input!\n' .
          $serializer->serialize($errors, 'json')
      );
    }

    return $this->render('registration/register.html.twig', [
      'registrationForm' => $form->createView()
    ]);
  }

  /**
   * @OA\Tag(name="Authorization")
   * @Route("/api/v1.0/register", name="api_register", methods={"POST"})
   */
  public function registerApi(
    Request $request,
    UserPasswordEncoderInterface $passwordEncoder,
    GuardAuthenticatorHandler $guardHandler,
    EmailPasswordAuthenticator $authenticator,
    ValidatorInterface $validator,
    LoggerInterface $logger,
    MySerializer $serializer
  ): Response {
    $email = $request->request->get('email') ?? '';
    $password = $request->request->get('password') ?? '';
    $name = $request->request->get('name') ?? '';
    $surname = $request->request->get('surname') ?? '';
    $patronymic = $request->request->get('patronymic') ?? '';
    $level = $request->request->get('level') ?? '';
    $birthday =
      $request->request->get('birthday') == null
        ? null
        : new \DateTime($request->request->get('birthday'));

    $fingerprint = $request->request->get('fingerprint');
    if ($fingerprint == null) {
      throw new ApiException(
        null,
        'Registration failed',
        'fingerprint must be provided for api registration',
        -1,
        Response::HTTP_BAD_REQUEST
      );
    }

    $user = new User();
    $user->setEmail($email);
    $user->setName($name);
    $user->setSurname($surname);
    $user->setPatronymic($patronymic);
    $user->setLevel($level);
    $user->setPassword($password);
    $user->setBirthday($birthday);

    $errors = $validator->validate($user);
    if (\count($errors) > 0) {
      $errorsArray = [];
      /** @var ConstraintViolationInterface $error */
      foreach ($errors as $error) {
        $errorsArray[$error->getPropertyPath()] = $error->getMessage();
      }
      $logger->alert(
        'Invalid registration data (in API)\n' .
          $serializer->serialize($errorsArray, 'json')
      );
      throw new ApiAuthorizationException(
        'Registration failed',
        $errorsArray,
        106,
        Response::HTTP_UNAUTHORIZED
      );
    }

    // we verified that password is not blank, so we can encode it
    $user->setPassword($passwordEncoder->encodePassword($user, $password));

    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->persist($user);
    $entityManager->flush();

    $this->sendVerificationEmail($user);

    $authenticator->setUser($user);
    return $guardHandler->authenticateUserAndHandleSuccess(
      $user,
      $request,
      $authenticator,
      'main' // firewall name in security.yaml
    );
  }

  /**
   * @Route("/verify/email", name="app_verify_email")
   */
  public function verifyUserEmail(
    Request $request,
    LoggerInterface $logger
  ): Response {
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

    // validate email confirmation link, sets User::isVerified=true and persists
    try {
      $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
    } catch (VerifyEmailExceptionInterface $verifyEmailExceptionInterface) {
      $logger->critical('Failed to verify email', [
        'message' => $verifyEmailExceptionInterface->getMessage(),
        'reason' => $verifyEmailExceptionInterface->getReason(),
        'trace' => $verifyEmailExceptionInterface->getTraceAsString()
      ]);
      $this->addFlash(
        'danger',
        'Во время подтверждения адреса электронной почты произошла ошибка.'
      );

      return $this->redirectToRoute('register');
    }

    $this->addFlash('success', 'Ваш адрес электронной почты был подтверждён.');

    return $this->redirectToRoute('profile');
  }

  private function sendVerificationEmail(User $user): void
  {
    // generate a signed url and email it to the user
    if ($this->getParameter('kernel.environment') == 'prod') {
      $this->emailVerifier->sendEmailConfirmation(
        'app_verify_email',
        $user,
        (new TemplatedEmail())
          ->from(new Address('no-reply@svetdevserver.tk', 'Mail Bot'))
          ->to($user->getEmail())
          ->subject('Пожалуйста, подтвердите ваш email')
          ->htmlTemplate('registration/confirmation_email.html.twig')
      );
    }

    // do anything else you need here, like send an email
    $this->addFlash(
      'info',
      'На Ваш адрес электронной почты было отправлено письмо для подтверждения регистрации.'
    );
  }
}
