<?php
namespace App\Service;

use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Ramsey\Uuid\Uuid;
use App\Exception\UnknownPhotoBucketException;
use App\Enum\PhotoBuckets;
use App\Entity\Photo;
use App\Entity\Document;

final class FileUploader
{
  /**
   * @var ContainerInterface
   */
  private $container;

  public function __construct(ContainerInterface $container)
  {
    $this->container = $container;
  }

  public function uploadDocument(UploadedFile $doc): Document
  {
    $document = new Document();
    $document
      ->setOriginalFilename($doc->getClientOriginalName())
      ->setFilename(
        $this->upload(
          $doc,
          $this->container->getParameter('documents_directory')
        )
      );
    return $document;
  }

  /**
   * Uploads image and extracts additional info required by the entity
   *
   * @throws UnknownPhotoBucketException
   * @throws FileException
   *
   * @param UploadedFile $file
   * @param string $targetDirectory
   * @return Photo photo
   */
  public function uploadImage(UploadedFile $image, string $bucket): Photo
  {
    $targetDirectory = $this->container->getParameter(
      PhotoBuckets::getFileLocationParameterKey($bucket)
    );

    $filename = $image->getClientOriginalName();
    $mimeType = $image->getMimeType();
    [$width, $height] = \getimagesize($image->getPathname());
    $serverFilename = $this->upload($image, $targetDirectory);

    $photo = new Photo();
    $photo
      ->setFilename($filename)
      ->setMimeType($mimeType)
      ->setServerFilename($serverFilename)
      ->setWidth($width)
      ->setHeight($height)
      ->setBucket($bucket);
    return $photo;
  }
  /**
   * Handles file upload
   *
   * @param UploadedFile $file
   * @param string $targetDirectory
   * @return string filename
   *
   * @throws FileException
   */
  private function upload(UploadedFile $file, string $targetDirectory): string
  {
    $fileName = Uuid::uuid4() . '.' . $file->getClientOriginalExtension();

    $file->move($targetDirectory, $fileName);

    return $fileName;
  }
}
