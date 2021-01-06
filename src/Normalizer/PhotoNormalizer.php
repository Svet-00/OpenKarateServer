<?php

namespace App\Normalizer;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Utils\EntityHelper;
use App\Entity\Photo;

final class PhotoNormalizer implements
  ContextAwareNormalizerInterface,
  NormalizerAwareInterface
{
  use NormalizerAwareTrait;
  /**
   * @var UrlGeneratorInterface
   */
  private $urlGenerator;

  public function __construct(UrlGeneratorInterface $urlGenerator)
  {
    $this->urlGenerator = $urlGenerator;
  }

  /** @inheritDoc */
  public function supportsNormalization(
    $data,
    string $format = null,
    $context = []
  ) {
    if (
      isset($context[ProxyNormalizerInterface::USED_PROXIES]) &&
      in_array(Photo::class, $context[ProxyNormalizerInterface::USED_PROXIES])
    ) {
      return false;
    }
    return $data instanceof Photo ||
      EntityHelper::isArrayOfType($data, Photo::class);
  }

  /** @inheritDoc */
  public function normalize($data, $format = '', $context = []): array
  {
    $urlCallback = function ($inner, $outer, $format): string {
      return $this->urlGenerator->generate(
        'get_image',
        [
          'bucket' => $outer->getBucket(),
          'filename' => $outer->getServerFilename()
        ],
        UrlGeneratorInterface::ABSOLUTE_URL
      );
    };

    $context[AbstractNormalizer::CALLBACKS]['url'] = $urlCallback;
    $context[AbstractNormalizer::IGNORED_ATTRIBUTES][] = 'serverFilename';

    $context[ProxyNormalizerInterface::USED_PROXIES][] = Photo::class;

    return $this->normalizer->normalize($data, $format, $context);
  }
}
