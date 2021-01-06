<?php
namespace App\Controller;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Exception;
use Doctrine\ORM\EntityManagerInterface;
use App\Utils\Uuid;
use App\Utils\PreconfiguredComponents;
use App\Service\PostService;
use App\Service\MySerializer;
use App\Service\FileUploader;
use App\Service\DocumentService;
use App\Repository\PostRepository;
use App\Exception\ApiException;
use App\Entity\Post;
use App\Entity\Photo;
use App\Entity\Document;

final class NewsController extends AbstractController
{
  /**
   * @var EntityManagerInterface
   */
  private $em;
  /**
   * @var LoggerInterface
   */
  private $logger;
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
    LoggerInterface $logger,
    MySerializer $normalizer
  ) {
    $this->em = $em;
    $this->logger = $logger;
    $this->normalizer = $normalizer;
  }

  /**
   * @OA\Response(
   *     response=200,
   *     description="List of posts",
   *     @OA\JsonContent(ref=@Model(type=Post::class))
   * )
   * @OA\Parameter(
   *     name="start",
   *     in="query",
   *     description="Offset in post list from which to start",
   *     @OA\Schema(type="integer")
   * )
   * @OA\Parameter(
   *     name="count",
   *     in="query",
   *     description="Max number of posts to fetch",
   *     @OA\Schema(type="integer")
   * )
   * @OA\Tag(name="News")
   * @Route("/api/v1.0/news", name="get_news", methods={"GET"}) */
  public function getPosts(Request $request): JsonResponse
  {
    $start = $request->query->getInt('start');
    $count = $request->query->getInt('count', 20);

    /** @var PostRepository */
    $repo = $this->em->getRepository(Post::class);
    $posts = $repo->getWithOffset($start, $count);
    $json = $this->normalizer->normalize($posts);
    return new JsonResponse($json);
  }

  /**
   * @OA\Response(
   *     response=200,
   *     description="Added post",
   *     @OA\JsonContent(ref=@Model(type=Post::class))
   * )
   * @OA\Parameter(
   *     name="text",
   *     in="query",
   *     description="Text of post",
   *     @OA\Schema(type="string")
   * )
   * @OA\Parameter(
   *     name="photo_urls",
   *     in="query",
   *     description="List of urls of post photos",
   *     @OA\Schema(type="string[]")
   * )
   * @OA\Tag(name="News")
   * @Route("/api/v1.0/posts/add", name="add_post", methods={"POST"})
   */
  public function addPost(Request $request): JsonResponse
  {
    $this->denyAccessUnlessGranted(self::ROLE_ADMIN);

    // TODO: добавление фото и документов

    $text = $request->query->get('text');

    $post = new Post();
    $post->setText($text);

    $this->em->persist($post);
    $this->em->flush($post);

    $json = $this->normalizer->normalize($post);
    return new JsonResponse($json);
  }

  /**
   * @OA\Response(
   *     response=200,
   *     description="Modified post",
   *     @OA\JsonContent(ref=@Model(type=Post::class))
   * )
   * @OA\Parameter(
   *     name="id",
   *     in="path",
   *     description="Id of post to edit",
   *     @OA\Schema(type="integer")
   * )
   * @OA\Parameter(
   *     name="text",
   *     in="query",
   *     description="Text of post",
   *     @OA\Schema(type="string")
   * )
   * @OA\Parameter(
   *     name="photo_urls",
   *     in="query",
   *     description="List of urls of post photos",
   *     @OA\Schema(type="string[]")
   * )
   * @OA\Tag(name="News")
   * @Route("/api/v1.0/posts/{id}/edit", name="edit_post", methods={"POST"})
   */
  public function editPost(Post $post, Request $request): JsonResponse
  {
    $this->denyAccessUnlessGranted(self::ROLE_ADMIN);
    // TODO: редактирование документов и фотографий
    $text = $request->query->get('text') ?? $post->getText();

    $post->setText($text);

    $this->em->persist($post);
    $this->em->flush($post);

    $json = $this->normalizer->normalize($post);
    return new JsonResponse($json);
  }

  /**
   * @OA\Response(
   *     response=200,
   *     description="Removed post",
   *     @OA\JsonContent(ref=@Model(type=Post::class))
   * )
   * @OA\Parameter(
   *     name="id",
   *     in="path",
   *     required=true,
   *     description="Id of post to remove",
   *     @OA\Schema(type="integer")
   * )
   * @OA\Tag(name="News")
   * @Route("/api/v1.0/posts/{id}/remove", name="remove_post", methods={"DELETE"})
   */
  public function removePost(Post $post): JsonResponse
  {
    $this->denyAccessUnlessGranted(self::ROLE_ADMIN);

    // TODO: if I will decouple documents from posts, this should be removed
    /** @var Document $document */
    foreach ($post->getDocuments() as $document) {
      try {
        \unlink(
          $this->getParameter('documents_directory') . $document->getFilename()
        );
      } catch (\Exception $exception) {
      } finally {
        $this->em->remove($document);
      }
    }

    /** @var Photo $photo */
    foreach ($post->getPhotos() as $photo) {
      try {
        \unlink(
          $this->getParameter('gallery_directory') . $photo->getServerFilename()
        );
      } catch (\Exception $exception) {
      } finally {
        $this->em->remove($photo);
      }
    }

    $this->em->remove($post);
    $this->em->flush();

    $json = $this->normalizer->normalize($post);
    return new JsonResponse($post);
  }

  /**
   * @OA\Response(
   *     response=200,
   *     description="Modified post",
   *     @OA\JsonContent(ref=@Model(type=Post::class))
   * )
   * @OA\Parameter(
   *     name="id",
   *     in="path",
   *     required=true,
   *     description="Id of post to add document to",
   *     @OA\Schema(type="integer")
   * )
   * @OA\RequestBody(
   *     required=true,
   *     description="Binary document",
   *     @OA\MediaType(
   *        mediaType="multipart/form-data",
   *        @OA\Schema(
   *            @OA\Property(
   *                property="document",
   *                type="string",
   *                format="binary"
   *            )
   *        )
   *     )
   * )
   * @OA\Tag(name="News")
   * @Route("/api/v1.0/posts/{id}/documents/add", name="add_post_document", methods={"POST"})
   * @return \Symfony\Component\HttpFoundation\JsonResponse|void
   */
  public function addPostDocument(
    Post $post,
    Request $request,
    FileUploader $fileUploader
  ) {
    $this->denyAccessUnlessGranted(self::ROLE_ADMIN);

    /** @var UploadedFile $doc */
    $document = $request->files->get('document');

    if ($document == null) {
      throw new ApiException(
        null,
        'Document upload failed',
        'Document is required',
        0,
        Response::HTTP_BAD_REQUEST
      );
    }

    try {
      $doc = $fileUploader->uploadDocument($document);
      $post->addDocument($doc);

      $this->em->persist($post);
      $this->em->flush();

      $json = $this->normalizer->normalize($post);
      return new JsonResponse($json);
    } catch (FileException $fileException) {
      throw new ApiException(
        $fileException,
        'Failed to upload file',
        'Server-side unknown error'
      );
    }
  }

  /**
   * @OA\Response(
   *     response=200,
   *     description="Deleted document",
   *     @OA\JsonContent(ref=@Model(type=Post::class))
   * )
   * @OA\Parameter(
   *     name="id",
   *     in="path",
   *     required=true,
   *     description="Id of document to delete",
   *     @OA\Schema(type="integer")
   * )
   * @OA\Tag(name="News")
   * @Route("/api/v1.0/posts/documents/{id}/remove", name="remove_post_document", methods={"DELETE"})
   */
  public function removePostDocument(Document $doc): JsonResponse
  {
    $this->denyAccessUnlessGranted(self::ROLE_ADMIN);

    try {
      \unlink($this->getParameter('documents_directory') . $doc->getFilename());

      $this->em->remove($doc);
      $this->em->flush();
    } catch (\Exception $exception) {
      $this->logger->critical(
        'Most likely the document is stored in db, but does not exist in filesystem.' .
          'It is cringe...\nOriginal error: ' .
          '\nDocument: ' .
          print_r($this->normalizer->normalize($doc))
      );
      throw $exception;
    }

    $json = $this->normalizer->normalize($doc);
    return new JsonResponse($json);
  }

  /*
  TODO: отделить документы от постов
   в будущем может возникнуть ситуация,
   что к нескольким постам нужно прикрепить один и тот же документ
   в данной ситуации удобнее сделать,
   чтобы посты и документы существовали отдельно
   в этом случае нам нужно отдельное api
   для манипулирования этими сущностями
   а еще в этом случае нам надо,
   чтобы у пользователя был список всех документов,
   возможность этим списком управлять
   и прикреплять ранее загруженные доки к постам
   делать это долго, муторно, и не факт, что востребовано
   так что вещь хорошая, если попросили или есть время + желание
   */
}
