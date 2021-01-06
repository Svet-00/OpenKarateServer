<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use LogicException;
use Google_Service_FirebaseCloudMessaging_SendMessageRequest;
use Google_Service_FirebaseCloudMessaging_Notification;
use Google_Service_FirebaseCloudMessaging_Message;
use Google_Service_FirebaseCloudMessaging_AndroidNotification;
use Google_Service_FirebaseCloudMessaging_AndroidConfig;
use Google_Service_FirebaseCloudMessaging;
use Google_Client;
use Exception;
use App\Utils\PreconfiguredComponents;
use App\Notifications\Notification;
use App\Enum\NotificationTopics;

final class PushNotificationService
{
  /** @var Google_Service_FirebaseCloudMessaging $fcmClient */
  private $fcmClient;
  /**
   * @var LoggerInterface
   */
  private $logger;
  /**
   * @var MySerizalizer
   */
  private $normalizer;
  /**
   * @var string
   */
  private $firebaseProjectName;

  public function __construct(
    string $authConfigFilePath,
    string $firebaseProjectName,
    LoggerInterface $logger,
    Google_Client $client,
    MySerializer $normalizer
  ) {
    $this->logger = $logger;
    $this->firebaseProjectName = $firebaseProjectName;

    $client->setAuthConfig($authConfigFilePath);
    $client->setRedirectUri('http://localhost');
    $client->addScope(Google_Service_FirebaseCloudMessaging::CLOUD_PLATFORM);

    $this->fcmClient = new Google_Service_FirebaseCloudMessaging($client);
  }

  public function sendForTopics(Notification $notification, array $topics): bool
  {
    $request = $this->constructRequest($notification, $topics);
    return $this->send($request);
  }

  public function sendToUser(Notification $notification, string $token): bool
  {
    $request = $this->constructRequest($notification, $token);
    return $this->send($request);
  }
  private function constructRequest(
    Notification $notification,
    $target
  ): Google_Service_FirebaseCloudMessaging_SendMessageRequest {
    $commonNotification = new Google_Service_FirebaseCloudMessaging_Notification();
    $commonNotification->setTitle($notification->getTitle());
    $commonNotification->setBody($notification->getBody());
    $commonNotification->setImage($notification->getImage());

    $androidNotification = new Google_Service_FirebaseCloudMessaging_AndroidNotification();
    $androidNotification->setDefaultSound(true);

    $androidConfig = new Google_Service_FirebaseCloudMessaging_AndroidConfig();
    $androidConfig->setNotification($androidNotification);

    $message = new Google_Service_FirebaseCloudMessaging_Message();
    $message->setNotification($commonNotification);
    $message->setAndroid($androidConfig);

    if (\is_array($target)) {
      if (\count($target) == 0) {
        throw new LogicException('List of topics can not be empty.');
      }
      foreach ($target as $topic) {
        if (!NotificationTopics::isValidValue($topic)) {
          $this->logger->warning(
            \sprintf(
              "Sending message to non-recognized topic. Actual topic was '%s'.",
              $topic
            )
          );
        }
      }

      if (\count($target) == 1) {
        $message->setTopic($target[0]);
      } else {
        $condition = "'" . \substr(\implode("' in topics &&", $target), 0, -3);
        $message->setCondition($condition);
      }
    } else {
      $message->setToken($target);
    }

    $request = new Google_Service_FirebaseCloudMessaging_SendMessageRequest();
    $request->setMessage($message);
    $request->setValidateOnly(false);
    return $request;
  }
  private function send(
    Google_Service_FirebaseCloudMessaging_SendMessageRequest $request
  ): bool {
    try {
      $message = $this->fcmClient->projects_messages->send(
        $this->firebaseProjectName,
        $request
      );
      return true;
    } catch (Exception $exception) {
      $this->logger->critical($this->normalizer->serialize($exception, 'json'));
      return false;
    }
  }
}
