<?php

namespace App\Controller;

use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\IniSizeFileException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Ramsey\Uuid\Uuid as UuidUuid;
use Exception;
use ErrorException;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;
use App\Utils\Uuid;
use App\Repository\UserRepository;
use App\Form\UserType;
use App\Form\UserPasswordType;
use App\Exception\ApiException;
use App\Enum\PhotoBuckets;
use App\Enum\AvatarFormats;
use App\Entity\User;
use App\Entity\Gym;

final class ProfileController extends AbstractController
{
  /**
   * @Route("/profile", name="profile")
   */
  public function profile(
    Request $request,
    UserPasswordEncoderInterface $passwordEncoder,
    EntityManagerInterface $entityManager
  ): Response {
    $user = $this->getUser();
    $form = $this->get('form.factory')->createNamed(
      'userForm',
      UserType::class,
      $user
    );

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $entityManager->persist($user);
      $entityManager->flush();
    }

    $passwordForm = $this->get('form.factory')->createNamed(
      'passwordForm',
      UserPasswordType::class,
      $user
    );
    $passwordForm->handleRequest($request);

    if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
      $user->setPassword(
        $passwordEncoder->encodePassword(
          $user,
          $passwordForm->get('plainPassword')->getData()
        )
      );
      $this->addFlash('success', 'Пароль успешно изменён.');
      $entityManager->persist($user);
      $entityManager->flush();
    }

    return $this->render('profile.twig', [
      'userForm' => $form->createView(),
      'passwordForm' => $passwordForm->createView()
    ]);
  }

  /**
   * @Route("/profile/change_avatar", name="change_avatar", methods={"POST"})
   */
  public function changeAvatar(
    Request $request,
    EntityManagerInterface $entityManager
  ): JsonResponse {
    if (
      $this->isCsrfTokenValid(
        'update-avatar',
        $request->request->get('token')
      ) == false
    ) {
      throw new InvalidCsrfTokenException();
    }

    try {
      /** @var UploadedFile $originalImage */
      $originalImage = $request->files->get('originalImg');
      /** @var UploadedFile $squareImage */
      $squareImage = $request->files->get('squareImg');
      /** @var UploadedFile $squareImage */
      $wideImage = $request->files->get('wideImg');

      // we can allow original image to be null when user just changes
      // avatar thumbnail and just rename original image on server
      // but when doing this we can't guarantee that original image is supposed
      // to be null
      // so when not really necessary, this approach gives us extra confidence
      // that there is no bug on frontend
      if (
        $squareImage == null ||
        $wideImage == null ||
        $originalImage == null
      ) {
        throw new ErrorException(
          'squareImage, wideImage and originalImage are required'
        );
      }

      // TODO: add voter for security
      // ? use App\Service\FileUploader
      // ? approach with file uploader requires another way to store/retrieve
      // ? filenames for different avatar's format but simplifies logic on both
      // ? backend and frontend
      $filenameTemplate =
        UuidUuid::uuid4() .
        '_{{ format }}.' .
        ($originalImage->guessExtension() ?? 'png');
      $avatarsDir = $this->getParameter('avatars_directory');

      /** @var User $user */
      $user = $this->getUser();

      if ($user->hasAvatar()) {
        \unlink($avatarsDir . $user->getAvatarFilename(AvatarFormats::Square));
        \unlink($avatarsDir . $user->getAvatarFilename(AvatarFormats::Wide));
        \unlink(
          $avatarsDir . $user->getAvatarFilename(AvatarFormats::Original)
        );
      }

      $user->setAvatarFilenameTemplate($filenameTemplate);

      $originalImage->move(
        $avatarsDir,
        $user->getAvatarFilename(AvatarFormats::Original)
      );
      $squareImage->move(
        $avatarsDir,
        $user->getAvatarFilename(AvatarFormats::Square)
      );
      $wideImage->move(
        $avatarsDir,
        $user->getAvatarFilename(AvatarFormats::Wide)
      );

      $entityManager->persist($user);
      $entityManager->flush();
    } catch (IniSizeFileException $iniSizeFileException) {
      $this->addFlash(
        'danger',
        'Размер загружаемого изображения не должен превышать 10Мб'
      );
    } catch (Exception $exception) {
      \dump($exception);
      // TODO: дифференцировать ошибки и кидать более релевантные исключения
      throw new ErrorException(
        'Avatar upload failed ' . $exception->getMessage(),
        0,
        1,
        __FILE__,
        __LINE__,
        $exception
      );
    }

    $json = [];
    foreach (AvatarFormats::toArray() as $format) {
      $json['url_' . $format] = $this->generateUrl('get_image', [
        'bucket' => PhotoBuckets::USERS,
        'filename' => $user->getAvatarFilename($format)
      ]);
    }

    return new JsonResponse($json);
  }

  /**
   * @Route("/profile/delete_avatar/{token}", name="delete_avatar", methods={"GET"})
   */
  public function deleteAvatar(
    Request $request,
    EntityManagerInterface $entityManager,
    string $token
  ): Response {
    if ($this->isCsrfTokenValid('delete-avatar', $token) == false) {
      throw new InvalidCsrfTokenException();
    }
    $user = $this->getUser();
    if ($user->hasAvatar()) {
      foreach (AvatarFormats::toArray() as $format) {
        try {
          \unlink(
            $this->getParameter('avatars_directory') .
              $user->getAvatarFilename($format)
          );
        } catch (Exception $exception) {
        }
      }
      $user->setAvatarFilenameTemplate(null);
      $entityManager->persist($user);
      $entityManager->flush();
    }
    return $this->redirectToRoute('profile');
  }
}
