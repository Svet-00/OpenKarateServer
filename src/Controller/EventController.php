<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Annotations as OA;
use Doctrine\ORM\EntityManagerInterface;
use App\Utils\PreconfiguredComponents;
use App\Service\PushNotificationService;
use App\Service\NotificationTopics;
use App\Service\MySerializer;
use App\Service\EventService;
use App\Entity\Event;

final class EventController extends AbstractController
{
  /**
   * @var EntityManagerInterface
   */
  private $em;
  /**
   * @var MySerializer
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
   * @OA\Tag(name="Events")
   * @Route("/api/v1.0/events", name="get_events", methods={"GET"})
   */
  public function getEvents(): JsonResponse
  {
    $events = $this->em->getRepository(Event::class)->findAll();
    return new JsonResponse($this->normalizer->normalize($events));
  }

  /**
   * @OA\Tag(name="Events")
   * @Route("/api/v1.0/events/{id}/participate", name="participate_in_event", methods={"POST"})
   */
  public function participate(Event $event): JsonResponse
  {
    $this->denyAccessUnlessGranted('ROLE_USER');
    $event->addParticipant($this->getUser());
    $this->em->persist($event);
    $this->em->flush();
    return new JsonResponse($this->normalizer->normalize($event));
  }

  /**
   * @OA\Tag(name="Events")
   * @Route("/api/v1.0/events/add", name="add_event", methods={"POST"})
   */
  public function addEvent(Request $request): void
  {
    $this->denyAccessUnlessGranted(self::ROLE_ADMIN);
    // TODO:
  }

  /**
   * @OA\Tag(name="Events")
   * @Route("/api/v1.0/events/{id}/remove", name="remove_event", methods={"DELETE"})
   */
  public function removeEvent(Event $event): void
  {
    $this->denyAccessUnlessGranted(self::ROLE_ADMIN);

    foreach ($event->getLinks() as $link) {
      $this->em->remove($link);
    }

    // TODO: if I will decouple documents from anything, this should be removed
    /** @var Document $document */
    foreach ($event->getDocuments() as $document) {
      try {
        \unlink(
          $this->getParameter('documents_directory') . $document->getFilename()
        );
      } catch (\Exception $exception) {
      } finally {
        $this->em->remove($document);
      }
    }

    $this->em->remove($event);
    $this->em->flush();
  }
}
