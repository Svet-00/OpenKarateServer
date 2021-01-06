<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Http\Discovery\Exception\NotFoundException;
use Exception;
use App\Enum\PhotoBuckets;

final class FileServeController extends AbstractController
{
  /**
   * @Route("/img/{bucket}/{filename}", name="get_image")
   * @return BinaryFileResponse|void
   */
  public function getImage(string $bucket, string $filename)
  {
    try {
      $path =
        $this->getParameter(
          PhotoBuckets::getFileLocationParameterKey($bucket)
        ) . $filename;
      return new BinaryFileResponse($path);
    } catch (Exception $exception) {
      throw new NotFoundException();
    }
  }

  /**
   * @Route("/docs/{filename}", name="get_document_file")
   * @return BinaryFileResponse|void
   */
  public function getDocument(string $filename)
  {
    $path = $this->getParameter('documents_directory') . $filename;
    try {
      return new BinaryFileResponse($path);
    } catch (Exception $exception) {
      throw new NotFoundException();
    }
  }
}
