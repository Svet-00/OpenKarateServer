<?php

namespace App\Controller;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\MySerializer\SerializerInterface;
use Symfony\Component\MySerializer\MySerializer\AbstractNormalizer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use App\Utils\PreconfiguredComponents;
use App\Service\UserService;
use App\Service\MySerializer;
use App\Repository\UserRepository;
use App\Enum\AvatarFormats;
use App\Entity\User;

final class UserController extends AbstractController
{
  /**
   * @var UserService
   */
  private $normalizer;

  public function __construct(MySerializer $normalizer)
  {
    $this->normalizer = $normalizer;
  }

  /**
   * @OA\Response(
   *     response=200,
   *     description="Array of users",
   *     @OA\JsonContent(
   *       ref=@Model(type=User::class, groups={"api"})
   *     )
   * )
   * @OA\Tag(name="User")
   * @Route("/api/v1.0/users", name="get_users", methods={"GET"})
   */
  public function GetUsers(): Response
  {
    $users = $this->getDoctrine()
      ->getRepository(User::class)
      ->findAll();

    return new JsonResponse($this->normalizer->normalize($users));
  }

  /**
   * @OA\Response(
   *     response=200,
   *     description="Array of trainers",
   *     @OA\JsonContent(
   *       ref=@Model(type=User::class, groups={"api"})
   *     )
   * )
   * @OA\Tag(name="User")
   * @Route("/api/v1.0/trainers", name="get_trainers", methods={"GET"})
   */
  public function GetTrainers(): Response
  {
    /**@var UserRepository $userRepository */
    $userRepository = $this->getDoctrine()->getRepository(User::class);
    $trainers = $userRepository->findByRole('ROLE_TRAINER');

    return new JsonResponse($this->normalizer->normalize($trainers));
  }

  /**
   * @OA\Response(
   *     response=200,
   *     description="Edited user",
   *     @OA\JsonContent(
   *       ref=@Model(type=User::class, groups={"api"})
   *     )
   * )
   * @OA\Parameter(
   *     name="id",
   *     in="path",
   *     required=true,
   *     description="Id of user to edit",
   *     @OA\Schema(type="string")
   * )
   * @OA\Parameter(
   *     name="level",
   *     in="query",
   *     required=true,
   *     description="User level. From '11 Кю' to '10 Дан' or '-'",
   *     @OA\Schema(type="string")
   * )
   * @OA\Parameter(
   *     name="admin",
   *     in="query",
   *     required=true,
   *     description="If user must be admin",
   *     @OA\Schema(type="bool")
   * )
   * @OA\Parameter(
   *     name="trainer",
   *     in="query",
   *     required=true,
   *     description="If user must be trainer",
   *     @OA\Schema(type="bool")
   * )
   * @OA\Tag(name="User")
   * @Route("/api/v1.0/user/{id}/edit", name="edit_any_user", methods={"POST"})
   */
  public function editAnyUser(
    User $user,
    Request $request,
    EntityManagerInterface $entityManager
  ): Response {
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
    $data = $request->request;

    $newLevel = $data->get('level') == '-' ? null : $data->get('level');
    $newIsAdmin = $data->get('admin') == 'true';
    $newIsTrainer = $data->get('trainer') == 'true';
    $currentRoles = $user->getRoles();

    $user->setLevel($newLevel);
    $user->setIsAdmin($newIsAdmin);
    $user->setIsTrainer($newIsTrainer);

    $entityManager->persist($user);
    $entityManager->flush();

    return new JsonResponse($this->normalizer->normalize($user));
  }
}
