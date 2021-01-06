<?php

namespace App\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Utils\EntityHelper;
use App\Entity\Link;

final class LinkNormalizer implements
  ContextAwareNormalizerInterface,
  NormalizerAwareInterface,
  ProxyNormalizerInterface
{
  use NormalizerAwareTrait;

  /**
   * @var UrlGeneratorInterface
   */
  private $urlGenerator;

  public function __construct()
  {
  }

  public function supportsNormalization(
    $data,
    string $format = null,
    $context = []
  ) {
    if (
      isset($context[ProxyNormalizerInterface::USED_PROXIES]) &&
      in_array(Link::class, $context[ProxyNormalizerInterface::USED_PROXIES])
    ) {
      return false;
    }

    return $data instanceof Link ||
      EntityHelper::isArrayOfType($data, Link::class);
  }

  /**
   * @inheritDoc
   */
  public function normalize($data, string $format = null, array $context = [])
  {
    $context[AbstractNormalizer::IGNORED_ATTRIBUTES][] = 'id';
    $context[ProxyNormalizerInterface::USED_PROXIES][] = Link::class;

    return $this->normalizer->normalize($data, $format, $context);
  }
}
