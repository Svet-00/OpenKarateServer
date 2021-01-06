<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use SebastianBergmann\CodeCoverage\Driver\Xdebug;
use Ramsey\Uuid\Uuid;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Doctrine\ORM\EntityManagerInterface;
use App\Utils\PreconfiguredComponents;
use App\Service\ScheduleService;
use App\Service\PhotoService;
use App\Service\MySerializer;
use App\Service\GymService;
use App\Service\GymPictureService;
use App\Service\FileUploader;
use App\Exception\ApiException;
use App\Enum\PhotoBuckets;
use App\Entity\User;
use App\Entity\Photo;
use App\Entity\GymPicture;
use App\Entity\Gym;

final class GymController extends AbstractController
{
  /** @var MySerializer */
  private $normalizer;

  /**
   * @var FileUploader
   */
  private $fileUploader;

  /**
   * @var EntityManagerInterface
   */
  private $em;

  /**
   * @var string
   */
  private const ROLE_ADMIN = 'ROLE_ADMIN';

  public function __construct(
    EntityManagerInterface $em,
    MySerializer $normalizer,
    FileUploader $fu
  ) {
    $this->em = $em;
    $this->normalizer = $normalizer;
    $this->fileUploader = $fu;
  }

  /**
   * @OA\Response(
   *     response=200,
   *     description="Array of gyms",
   *     @OA\JsonContent(
   *        type="array",
   *        @OA\Items(ref=@Model(type=Gym::class))
   *     )
   * )
   * @OA\Tag(name="Gym")
   * @Route("/api/v1.0/gyms", name="get_gyms", methods={"GET"})
   */
  public function getGyms(): JsonResponse
  {
    $gyms = $this->getDoctrine()
      ->getRepository(Gym::class)
      ->findAll();

    $supports = $this->normalizer->supportsNormalization($gyms);
    $json = $this->normalizer->normalize($gyms);

    return new JsonResponse($json);
  }

  /**
   * @OA\Response(
   *     response=200,
   *     description="Array of schedules related to this gym",
   *     @OA\JsonContent(
   *        type="array",
   *        @OA\Items(ref=@Model(type=Gym::class))
   *     )
   * )
   * @OA\Parameter(
   *     name="id",
   *     in="path",
   *     description="Gym id",
   *     @OA\Schema(type="integer")
   * )
   * @OA\Tag(name="Gym")
   *
   * @Route("/api/v1.0/gyms/{id}/schedules", name="get_gym_schedules", methods={"GET"})
   */
  public function getSchedules(Gym $gym): JsonResponse
  {
    $schedules = $gym->getSchedules();

    $json = $this->normalizer->normalize($schedules);
    return new JsonResponse($json);
  }

  /**
   * @OA\Response(
   *     response=200,
   *     description="New gym",
   *     @OA\JsonContent(
   *        ref=@Model(type=Gym::class)
   *     )
   * )
   * @OA\Parameter(
   *     name="title",
   *     in="query",
   *     required=true,
   *     description="Gym title (i.e. short name)",
   *     @OA\Schema(type="string")
   * )
   * @OA\Parameter(
   *     name="address",
   *     in="query",
   *     required=true,
   *     description="Gym address",
   *     @OA\Schema(type="string")
   * )
   * @OA\Parameter(
   *     name="address",
   *     in="query",
   *     required=true,
   *     description="Gym address",
   *     @OA\Schema(type="string")
   * )
   * @OA\Parameter(
   *     name="email",
   *     in="query",
   *     required=true,
   *     description="Gym email address",
   *     @OA\Schema(type="string")
   * )
   * @OA\Parameter(
   *     name="phone_number",
   *     in="query",
   *     required=true,
   *     description="Gym phone number",
   *     @OA\Schema(type="string")
   * )
   * @OA\Parameter(
   *     name="vk_link",
   *     in="query",
   *     required=true,
   *     description="Gym vk link",
   *     @OA\Schema(type="string")
   * )
   * @OA\Parameter(
   *     name="working_time",
   *     in="query",
   *     required=true,
   *     description="Gym working time",
   *     @OA\Schema(type="string")
   * )
   * @OA\Parameter(
   *     name="description",
   *     in="query",
   *     required=true,
   *     description="Gym description",
   *     @OA\Schema(type="string")
   * )
   * @OA\Tag(name="Gym")
   * @Route("/api/v1.0/gyms/add", name="add_gym", methods={"POST"})
   */
  public function addGym(Request $request): JsonResponse
  {
    $this->denyAccessUnlessGranted(self::ROLE_ADMIN);
    $data = $request->request;

    $gym = new Gym();
    $gym->setAddress($data->get('address'));
    $gym->setDescription($data->get('description'));
    $gym->setEmail($data->get('email'));
    $gym->setPhoneNumber($data->get('phone_number'));
    $gym->setTitle($data->get('title'));
    $gym->setVkLink($data->get('vk_link'));
    $gym->setWorkingTime($data->get('working_time'));

    $this->em->persist($gym);
    $this->em->flush();

    $json = $this->normalizer->normalize($gym);

    return new JsonResponse($json);
  }

  /**
   * @OA\Response(
   *     response=200,
   *     description="Edited gym",
   *     @OA\JsonContent(
   *        ref=@Model(type=Gym::class)
   *     )
   * )
   * @OA\Tag(name="Gym")
   * @Route("/api/v1.0/gyms/{id}/edit", name="edit_gym", methods={"POST"})
   */
  public function editGym(Gym $gym, Request $request): JsonResponse
  {
    $this->denyAccessUnlessGranted(self::ROLE_ADMIN);
    $data = $request->request;

    $gym->setAddress($data->get('address'));
    $gym->setDescription($data->get('description'));
    $gym->setEmail($data->get('email'));
    $gym->setPhoneNumber($data->get('phone_number'));
    $gym->setTitle($data->get('title'));
    $gym->setVkLink($data->get('vk_link'));
    $gym->setWorkingTime($data->get('working_time'));

    $this->em->persist($gym);
    $this->em->flush();

    $json = $this->normalizer->normalize($gym);

    return new JsonResponse($json);
  }

  /**
   * @OA\Response(
   *     response=200,
   *     description="Deleted gym",
   *     @OA\JsonContent(
   *        ref=@Model(type=Gym::class)
   *     )
   * )
   * @OA\Tag(name="Gym")
   * @Route("/api/v1.0/gyms/{id}/remove", name="delete_gym", methods={"DELETE"})
   */
  public function deleteGym(Gym $gym): JsonResponse
  {
    $this->denyAccessUnlessGranted(self::ROLE_ADMIN);

    $this->em->remove($gym);
    $this->em->flush();

    $json = $this->normalizer->normalize($gym);

    return new JsonResponse($json);
  }

  /**
   * @OA\Response(
   *     response=200,
   *     description="New gym picture",
   *     @OA\JsonContent(
   *        ref=@Model(type=GymPicture::class)
   *     )
   * )
   * @OA\Tag(name="Gym")
   * @Route("/api/v1.0/gyms/{id}/pictures/add", name="add_gym_picture", methods={"POST"})
   * @return JsonResponse
   */
  public function addGymPicture(Gym $gym, Request $request)
  {
    $this->denyAccessUnlessGranted(self::ROLE_ADMIN);

    try {
      $file = $request->files->get('file');
      $photo = $this->fileUploader->uploadImage($file, PhotoBuckets::GYMS);

      $gym->addPhoto($photo);
      $this->em->persist($photo);
      $this->em->persist($gym);
      $this->em->flush();
      return new JsonResponse($this->normalizer->normalize($photo));
    } catch (FileException $fileException) {
      throw new ApiException(
        $fileException,
        'Failed to upload file',
        $fileException->getMessage()
      );
    }
  }

  /**
   * @OA\Response(
   *     response=200,
   *     description="Deleted gym picture",
   *     @OA\JsonContent(
   *        ref=@Model(type=GymPicture::class)
   *     )
   * )
   * @OA\Tag(name="Gym")
   * @Route("/api/v1.0/gyms/pictures/{id}/remove", name="delete_gym_picture", methods={"DELETE"})
   * @return \Symfony\Component\HttpFoundation\JsonResponse|void
   */
  public function deleteGymPicture(
    Photo $photo,
    EntityManagerInterface $entityManager
  ) {
    $this->denyAccessUnlessGranted(self::ROLE_ADMIN);

    $entityManager->beginTransaction();
    try {
      \unlink(
        $this->getParameter(
          PhotoBuckets::getFileLocationParameterKey($photo->getBucket())
        ) . $photo->getServerFilename()
      );
      $entityManager->remove($photo);
      $entityManager->flush();
      $entityManager->commit();
      return new JsonResponse($this->normalizer->normalize($photo));
    } catch (\Exception $exception) {
      $entityManager->rollback();
      throw $exception;
    }
  }

  /**
   * @OA\Tag(name="Gym")
   * @Route("/api/v1.0/gyms/{id}/add-to-favorites", name="gym_add_to_favorites", methods={"POST"})
   * @return \Symfony\Component\HttpFoundation\JsonResponse|void
   */
  public function addGymToFavorites(Gym $gym)
  {
    $this->denyAccessUnlessGranted('ROLE_USER');
    /** @var User $user */
    $user = $this->getUser();
    $user->addfavoriteGym($gym);
    $this->em->persist($user);
    $this->em->flush();
    return new JsonResponse(['success' => true]);
  }

  /**
   * @OA\Tag(name="Gym")
   * @Route("/api/v1.0/gyms/{id}/remove-from-favorites", name="gym_remove_from_favorites", methods={"POST"})
   * @return \Symfony\Component\HttpFoundation\JsonResponse|void
   */
  public function removeGymFromFavorites(Gym $gym)
  {
    $this->denyAccessUnlessGranted('ROLE_USER');
    /** @var User $user */
    $user = $this->getUser();
    $user->removefavoriteGym($gym);
    $this->em->persist($user);
    $this->em->flush();
    return new JsonResponse(['success' => true]);
  }
}
