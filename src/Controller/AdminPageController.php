<?php

namespace App\Controller;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Form;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;
use App\Utils\EntityHelper;
use App\Service\PushNotificationService;
use App\Service\FileUploader;
use App\Notifications\Notification;
use App\Form\PostType;
use App\Form\PostEditType;
use App\Form\NotificationType;
use App\Form\EventType;
use App\Exception\ApiException;
use App\Enum\PhotoBuckets;
use App\Enum\NotificationTopics;
use App\Entity\User;
use App\Entity\Post;
use App\Entity\Photo;
use App\Entity\Link;
use App\Entity\Gym;
use App\Entity\Event;
use App\Entity\Document;

final class AdminPageController extends AbstractController
{
  /**
   * @var EntityManagerInterface
   */
  private $entityManager;
  /**
   * @var PushNotificationService
   */
  private $notificationService;
  /**
   * @var string
   */
  private const FLASH_SUCCESS = 'success';
  /**
   * @var string
   */
  private const FLASH_DANGER = 'danger';
  /**
   * @var string
   */
  private const FORM = 'form';
  /**
   * @var string
   */
  private const ID = 'id';
  /**
   * @var string
   */
  private const DOCUMENTS = 'documents';

  public function __construct(
    EntityManagerInterface $em,
    PushNotificationService $ns
  ) {
    $this->entityManager = $em;
    $this->notificationService = $ns;
  }

  /**
   * @Route("/admin/dashboard", name="admin_dashboard")
   */
  public function dashboard(): Response
  {
    /** @var UserRepository $userRepository */
    $userRepository = $this->getDoctrine()->getRepository(User::class);
    return $this->render('admin/dashboard.twig', [
      'registeredUsersCount' => $userRepository->getUserCount(),
      'registeredTodayUsersCount' => $userRepository->getUserCountByRegistrationDate(
        new DateTimeImmutable('now')
      )
    ]);
  }

  /**
   * @Route("/admin/notifications", name="admin_notifications")
   */
  public function notifications(
    Request $request,
    FileUploader $fileUploader
  ): Response {
    $notification = new Notification();

    /** @var Form $form */
    $form = $this->get('form.factory')->createNamed(
      'notificationForm',
      NotificationType::class,
      $notification
    );

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      try {
        if ($imageFile = $form->get('image')->getData()) {
          $image = $fileUploader->uploadImage(
            $imageFile,
            PhotoBuckets::NOTIfICATIONS
          );
          $notification->setImage(
            $this->generateUrl(
              'get_image',
              [
                'bucket' => $image->getBucket(),
                'filename' => $image->getServerFilename()
              ],
              UrlGeneratorInterface::ABSOLUTE_URL
            )
          );
        }

        $success = $this->notificationService->sendForTopics($notification, [
          NotificationTopics::GENERAL
        ]);
        if ($success) {
          $this->addFlash(self::FLASH_SUCCESS, 'Уведомление отправлено ✅');
        } else {
          $this->addFlash(self::FLASH_DANGER, 'Уведомление не отправлено ☹');
        }
      } catch (FileException $fileException) {
        $this->addFlash(
          self::FLASH_DANGER,
          'Не удалось загрузить изображение для уведомления ☹. Уведомление не было отправлено.'
        );
      }
      return $this->redirect($request->getPathInfo());
    }

    return $this->render('admin/notifications.twig', [
      self::FORM => $form->createView()
    ]);
  }

  /**
   * @Route("/admin/news", name="admin_news")
   */
  public function news(Request $request, FileUploader $fileUploader): Response
  {
    $post = new Post();
    $postRepository = $this->entityManager->getRepository(Post::class);
    $edit = false;

    if (
      $request->query->get('action') == 'edit' &&
      ($postId = $request->query->get('post_id')) != null
    ) {
      $edit = true;
      $post = $postRepository->findOneBy([self::ID => $postId]);
    }
    $formType = $edit ? PostEditType::class : PostType::class;
    /** @var Form $form */
    $form = $this->get('form.factory')->createNamed(
      'postForm',
      $formType,
      $post
    );

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      if ($formType == PostType::class) {
        try {
          /** @var UploadedFile $photoFile */
          if ($photoFile = $form->get('photo')->getData()) {
            $photo = $fileUploader->uploadImage(
              $photoFile,
              PhotoBuckets::GALLERY
            );
            $post->addPhoto($photo);
            $this->entityManager->persist($photo);
          }
          unset($photoFile);

          if ($form->get(self::DOCUMENTS)->getData() != null) {
            foreach ($form->get(self::DOCUMENTS)->getData() as $document) {
              $doc = $fileUploader->uploadDocument($document);
              $this->entityManager->persist($doc);
              $post->addDocument($doc);
            }
          }
          $this->entityManager->persist($post);
          $this->entityManager->flush();
          $this->addFlash(self::FLASH_SUCCESS, 'Новость опубликована ✅');
          $notification = new Notification();
          $notification->setTitle('Новая новость')->setBody($post->getText());
          $this->notificationService->sendForTopics($notification, [
            NotificationTopics::NEWS_POST_ADDED
          ]);
        } catch (FileException $fileException) {
          $this->addFlash(
            'error',
            'Не удалось загрузить один из файлов. Пожалуйста, попробуйте повторить отправку.'
          );
        }
      } else {
        $this->entityManager->persist($post);
        $this->entityManager->flush();
        $this->addFlash(self::FLASH_SUCCESS, 'Новость обновлена ✅');
        $notification = new Notification();
        $notification->setTitle('Новость обновлена')->setBody($post->getText());
        $this->notificationService->sendForTopics($notification, [
          NotificationTopics::NEWS_POST_UPDATED
        ]);
      }
      return $this->redirect($request->getPathInfo());
    }
    $errors = $form->getErrors(true, true);
    if ($errors->count() > 0) {
      /** @var FormError $error */
      foreach ($errors as $error) {
        $this->addFlash(self::FLASH_DANGER, $error->getMessage());
      }
    }

    $posts = $postRepository->findAll();

    return $this->render('admin/news.twig', [
      self::FORM => $form->createView(),
      'posts' => $posts
    ]);
  }

  /**
   * @Route("/admin/events", name="admin_events")
   */
  public function events(Request $request, FileUploader $fileUploader): Response
  {
    $event = new Event();
    $edit = $request->query->get('action') == 'edit';
    $eventId = $request->query->get('event_id');

    if ($edit && $eventId != null) {
      $event = $this->entityManager
        ->getRepository(Event::class)
        ->findOneBy([self::ID => $eventId]);
    }

    /** @var Form $form */
    $form = $this->get('form.factory')->createNamed(
      'eventForm',
      EventType::class,
      $event
    );

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      try {
        if ($form->get('links')->getData() != null) {
          foreach ($form->get('links')->getData() as $link) {
            $this->entityManager->persist($link);
            $event->addLink($link);
          }
        }
        if ($form->get(self::DOCUMENTS)->getData() != null) {
          foreach ($form->get(self::DOCUMENTS)->getData() as $document) {
            $doc = $fileUploader->uploadDocument($document);
            $this->entityManager->persist($doc);
            $event->addDocument($doc);
          }
        }
        $this->entityManager->persist($event);
        $this->entityManager->flush();

        if ($edit) {
          $this->addFlash(self::FLASH_SUCCESS, 'Событие обновлено ✅');
        } else {
          $this->addFlash(self::FLASH_SUCCESS, 'Событие добавлено ✅');
        }
        $notification = new Notification();
        $notification
          ->setTitle($event->getTitle())
          ->setBody($event->getDescription() ?? $event->getAddress());
        $this->notificationService->sendForTopics($notification, [
          $edit
            ? NotificationTopics::EVENT_UPDATED
            : NotificationTopics::EVENT_ADDED
        ]);
      } catch (\Exception $exception) {
        $this->addFlash(
          'error',
          'Не удалось загрузить один из файлов. Пожалуйста, попробуйте повторить отправку.'
        );
      }
      return $this->redirect($request->getPathInfo());
    }
    $errors = $form->getErrors(true, true);
    if ($errors->count() > 0) {
      /** @var FormError $error */
      foreach ($errors as $error) {
        $this->addFlash(self::FLASH_DANGER, $error->getMessage());
      }
    }

    $events = $this->entityManager
      ->getRepository(Event::class)
      ->findBy([], [self::ID => 'DESC']);
    $view = $form->createView();

    return $this->render('admin/events.twig', [
      self::FORM => $view,
      'events' => $events
    ]);
  }

  /**
   * @Route("/admin/gyms", name="admin_gyms")
   */
  public function gyms(): Response
  {
    return $this->render('admin/gyms.twig', []);
  }

  /**
   * @Route("/admin/users", name="admin_users")
   */
  public function users(): Response
  {
    return $this->render('admin/users.twig', []);
  }

  /**
   * @Route("/admin/schedule", name="admin_schedule")
   */
  public function schedule(): Response
  {
    $gyms = $this->getDoctrine()
      ->getRepository(Gym::class)
      ->findAll();
    return $this->render('admin/schedule.twig', ['gyms' => $gyms]);
  }
}
