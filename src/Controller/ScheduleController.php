<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\MySerializer\MySerializer\AbstractNormalizer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Doctrine\ORM\EntityManagerInterface;
use App\Utils\PreconfiguredComponents;
use App\Service\ScheduleService;
use App\Service\MySerializer;
use App\Entity\Schedule;
use App\Entity\Gym;

final class ScheduleController extends AbstractController
{
  /**
   * @var EntityManagerInterface
   */
  private $em;
  /**
   * @var ScheduleService
   */
  private $normalizer;
  /**
   * @var string
   */
  private const ROLE_ADMIN = 'ROLE_ADMIN';
  public function __construct(
    EntityManagerInterface $em,
    MySerializer $normalizer
  ) {
    $this->em = $em;
    $this->normalizer = $normalizer;
  }

  /**
   * @OA\Response(
   *     response=200,
   *     description="Array of schedules",
   *     @OA\JsonContent(
   *        type="array",
   *        @OA\Items(ref=@Model(type=Schedule::class))
   *     )
   * )
   * @OA\Tag(name="Schedule")
   * @Route("/api/v1.0/schedules", name="get_schedules", methods={"GET"})
   */
  public function getSchedules(): JsonResponse
  {
    $schedules = $this->getDoctrine()
      ->getRepository(Schedule::class)
      ->findAll();

    $json = $this->normalizer->normalize($schedules);
    return new JsonResponse($json);
  }

  /**
   * @OA\Response(
   *     response=200,
   *     description="Edited schedule",
   *     @OA\JsonContent(ref=@Model(type=Schedule::class))
   * )
   * @OA\Parameter(
   *     name="id",
   *     in="path",
   *     description="Id of schedule to be edited.",
   *     @OA\Schema(type="integer")
   * )
   * @OA\Parameter(
   *     name="day_of_week",
   *     in="query",
   *     description="New value for 'day of week' property",
   *     @OA\Schema(type="string")
   * )
   * @OA\Parameter(
   *     name="time",
   *     in="query",
   *     description="New value for 'time' property",
   *     @OA\Schema(type="string")
   * )
   * @OA\Parameter(
   *     name="description",
   *     in="query",
   *     description="New value for 'description' property",
   *     @OA\Schema(type="string")
   * )
   * @OA\Tag(name="Schedule")
   * @Route("/api/v1.0/schedules/{id}/edit", name="edit_schedule", methods={"POST"})
   */
  public function editSchedule(
    Schedule $schedule,
    Request $request
  ): JsonResponse {
    $this->denyAccessUnlessGranted(self::ROLE_ADMIN);
    $description = $request->request->get('description');
    $time = $request->request->get('time');
    $dayOfWeek = $request->request->get('day_of_week');

    $schedule->setDescription($description ?? $schedule->getDescription());
    $schedule->setTime($time ?? $schedule->getTime());
    $schedule->setDayOfWeek($dayOfWeek ?? $schedule->getDayOfWeek());

    $this->em->persist($schedule);
    $this->em->flush();

    return new JsonResponse($this->normalizer->normalize($schedule));
  }

  /**
   * @OA\Response(
   *     response=200,
   *     description="Created schedule",
   *     @OA\JsonContent(ref=@Model(type=Schedule::class))
   * )
   * @OA\Parameter(
   *     name="id",
   *     in="query",
   *     required=true,
   *     description="Valid id of gym new schedule should belong to.",
   *     @OA\Schema(type="integer")
   * )
   * @OA\Parameter(
   *     name="day_of_week",
   *     in="query",
   *     required=true,
   *     description="New value for 'day of week' property",
   *     @OA\Schema(type="string")
   * )
   * @OA\Parameter(
   *     name="time",
   *     in="query",
   *     required=true,
   *     description="New value for 'time' property",
   *     @OA\Schema(type="string")
   * )
   * @OA\Parameter(
   *     name="description",
   *     in="query",
   *     required=true,
   *     description="New value for 'description' property",
   *     @OA\Schema(type="string")
   * )
   * @OA\Tag(name="Schedule")
   * @Route("/api/v1.0/schedules/add", name="add_schedule", methods={"POST"})
   */
  public function addSchedule(Request $request): JsonResponse
  {
    $this->denyAccessUnlessGranted(self::ROLE_ADMIN);
    $schedule = new Schedule();
    $schedule->setDescription($request->request->get('description'));
    $schedule->setTime($request->request->get('time'));
    $schedule->setDayOfWeek($request->request->get('day_of_week'));

    $gym = $this->getDoctrine()
      ->getRepository(Gym::class)
      ->findOneBy(['id' => $request->request->get('gym_id')]);

    $schedule->setGym($gym);

    $this->em->persist($schedule);
    $this->em->persist($schedule);
    $this->em->flush();

    return new JsonResponse($this->normalizer->normalize($schedule));
  }

  /**
   * @OA\Response(
   *     response=200,
   *     description="Deleted schedule",
   *     @OA\JsonContent(ref=@Model(type=Schedule::class))
   * )
   * @OA\Parameter(
   *     name="id",
   *     in="path",
   *     description="Id of schedule to delete.",
   *     @OA\Schema(type="integer")
   * )
   * @OA\Tag(name="Schedule")
   * @Route("/api/v1.0/schedules/{id}/remove", name="delete_schedule", methods={"DELETE"})
   */
  public function removeSchedule(
    Schedule $schedule,
    Request $request
  ): JsonResponse {
    $this->denyAccessUnlessGranted(self::ROLE_ADMIN);
    $this->em->remove($schedule);
    $this->em->flush();

    return new JsonResponse($this->normalizer->normalize($schedule));
  }
}
