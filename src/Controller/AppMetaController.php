<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\MySerializer;
use App\Notifications\NotificationTopic;
use App\Enum\NotificationTopics;

class AppMetaController extends AbstractController
{
  /**
   * @var MySerializer
   */
  private $normalizer;

  private const CURRENT_ANDROID_APP_VER = '1.0.0';
  private const CURRENT_IOS_APP_VER = '0.0.0';

  public function __construct(MySerializer $normalizer)
  {
    $this->normalizer = $normalizer;
  }

  /**
   * @Route("/api/{ver}/meta/app", name="get_app_meta")
   */
  public function meta(): JsonResponse
  {
    return new JsonResponse([
      'latest_version' => [
        'android' => self::CURRENT_ANDROID_APP_VER,
        'ios' => self::CURRENT_IOS_APP_VER
      ],
      'notification_topics' => $this->getNotificationTopics()
    ]);
  }

  /**
   * @Route("/api/{ver}/meta/app/notification-topics", name="get_app_notification_topics")
   */
  public function topics(): JsonResponse
  {
    return new JsonResponse($this->getNotificationTopics());
  }

  private function getNotificationTopics(): array
  {
    $general = new NotificationTopic();
    $general
      ->setStringRepresentation(NotificationTopics::GENERAL)
      ->setDescription('Другие уведомления')
      ->setImportant(false);

    $newsPostAdded = new NotificationTopic();
    $newsPostAdded
      ->setStringRepresentation(NotificationTopics::NEWS_POST_ADDED)
      ->setDescription('Опубликована новая новость')
      ->setImportant(true);
    $newsPostUpdated = new NotificationTopic();
    $newsPostUpdated
      ->setStringRepresentation(NotificationTopics::NEWS_POST_UPDATED)
      ->setDescription('Новость была отредактирована')
      ->setImportant(true);

    $eventAdded = new NotificationTopic();
    $eventAdded
      ->setStringRepresentation(NotificationTopics::EVENT_ADDED)
      ->setDescription('Появилось новое событие')
      ->setImportant(true);
    $eventUpdated = new NotificationTopic();
    $eventUpdated
      ->setStringRepresentation(NotificationTopics::EVENT_UPDATED)
      ->setDescription('В событиях произошли изменения')
      ->setImportant(true);
    $scheduleUpdated = new NotificationTopic();
    $scheduleUpdated
      ->setStringRepresentation(NotificationTopics::SCHEDULE_UPDATED)
      ->setDescription('Изменения в расписании избранных залов')
      ->setImportant(true);

    $topics = [
      $general,
      $newsPostAdded,
      $newsPostUpdated,
      $eventAdded,
      $eventUpdated,
      $scheduleUpdated
    ];
    return $this->normalizer->normalize($topics);
  }
}
