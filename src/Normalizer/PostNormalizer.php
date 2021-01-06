<?php

namespace App\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Doctrine\ORM\PersistentCollection;
use Doctrine\Common\Collections\Collection;
use App\Utils\EntityHelper;
use App\Entity\Post;

final class PostNormalizer implements
  ContextAwareNormalizerInterface,
  NormalizerAwareInterface
{
  use NormalizerAwareTrait;
  /**
   * @var DocumentService
   */
  private $documentNormalizer;
  /**
   * @var PhotoService
   */
  private $photoNormalizer;

  public function __construct(
    DocumentNormalizer $documentNormalizer,
    PhotoNormalizer $photoNormalizer
  ) {
    $this->documentNormalizer = $documentNormalizer;
    $this->photoNormalizer = $photoNormalizer;
  }

  /** @inheritDoc */
  public function supportsNormalization(
    $data,
    string $format = null,
    $context = []
  ) {
    if (
      isset($context[ProxyNormalizerInterface::USED_PROXIES]) &&
      in_array(Post::class, $context[ProxyNormalizerInterface::USED_PROXIES])
    ) {
      return false;
    }
    return $data instanceof Post ||
      EntityHelper::isArrayOfType($data, Post::class);
  }

  /**
   * @inheritDoc
   */
  public function normalize($data, $format = '', $context = [])
  {
    $documentCallback = function ($inner, $outer, $format) {
      return $this->documentNormalizer->normalize($inner);
    };

    $photoCallback = function ($inner, $outer, $format): Collection {
      $callback = function ($data): array {
        return $this->photoNormalizer->normalize($data);
      };
      /** @var PersistentCollection $inner */
      return $inner->map($callback);
    };

    $context[AbstractNormalizer::CALLBACKS]['documents'] = $documentCallback;
    $context[AbstractNormalizer::CALLBACKS]['photos'] = $photoCallback;

    $context[ProxyNormalizerInterface::USED_PROXIES][] = Post::class;

    return $this->normalizer->normalize($data, $format, $context);
  }
}
